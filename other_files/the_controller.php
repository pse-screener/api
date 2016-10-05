<?php
/*
 * This handles updating the data in the database's materialized views
 * 
 * 
 */

class ViewBuilderCron extends Controller {

	public function ViewBuilderCron() {
		parent::Controller();

		// Only run if we're started from one of the command line scripts
		if(basename($_SERVER['SCRIPT_FILENAME']) != 'build_mat_views_network.php'
				&& basename($_SERVER['SCRIPT_FILENAME']) != 'build_mat_views_recruiter.php')
			exit;
	}

	private function getCutoffTimestamp() {
		$excludeLastMinutes = $this->config->item("MaterializedViewExcludeLastMinutes");

		$sql = "select date_sub(now(), interval ? minute) as cutoff";
		$query = $this->db->query($sql, Array($excludeLastMinutes));
		if (!$query) {
			header("HTTP/1.1 500 Internal Server Error");
			$msg = $this->db->_error_message();
			$num = $this->db->_error_number();
			log_message("error", "Database Error querying cutoff time: ($num) $msg");
			return false;
		}
		if ($query->num_rows() == 1) {
			$row = $query->row_array();
			return $row['cutoff'];
		} else {
			log_message("error", "Failed to obtain cutoff time.  SQL: $sql");
			return false;
		}
	}
	
	public function index($incremental, $requested_jt_recruiter_id, $verbose) {
		global $webvars;

		if ($this->config->item("ReadOnlyMode")) {
			echo "Data changes are not currently allowed.\n";
			return false;
		}

		$cutoffTimestamp = $this->getCutoffTimestamp();
		if ($cutoffTimestamp == false) {
			if ($verbose)
				echo "Failed to get cutoff timestamp; aborting.\n";
			return false;
		}

		if ($verbose) {
			echo "Building materialized views with data up through $cutoffTimestamp.\n";
		}

		// Replace the CodeIgniter default DB with the repmain database
		//
		// This needs to happen before either (1) we query rs_recruiter_source_stats"
		// for the recruiter IDs or (2) we call the update functions in the model.
		$master_conn_id = $this->db->conn_id;
		$this->db->conn_id = @mysqli_connect($webvars["REPMAIN_HOST"],
				$webvars["REPMAIN_USER"],
				$webvars["REPMAIN_PWD"],
				$webvars["REPMAIN_DB"]);
		if (!$this->db->conn_id) {
			log_message("error", "Unable to connect to repmain slave DB: (" . mysqli_connect_errno() . ") " . mysqli_connect_error());
			return false;
		}


		if ($requested_jt_recruiter_id == "network") {
			$this->updateNetworkViews($cutoffTimestamp, $verbose, $master_conn_id);
		} else {
			if (is_numeric($requested_jt_recruiter_id)) {
				$jt_recruiter_ids = Array($requested_jt_recruiter_id);

				// Since the user requested a specific
				// jt_recruiter_id, no need to prevent parallel processes
				$max_procs = 0;
			} else {
				$max_procs = $this->config->item("MaterializedViewMaxRecruiterProcesses");

				$jt_recruiter_ids = Array();

				// The reason we include rs_recruiter_source_stats is because we
				// may have generated stats from a campaign and then its recruiter
				// ID got changed afterwards
				$sql = "select distinct jt_recruiter_id from rs_advertisement
					union
					select distinct jt_recruiter_id from rs_recruiter_source_stats";
				$query = $this->db->query($sql);
				if (!$query) {
					header("HTTP/1.1 500 Internal Server Error");
					$msg = $this->db->_error_message();
					$num = $this->db->_error_number();
					log_message("error", "Database Error querying recruiter IDs: ($num) $msg");
					return false;
				}
				foreach ($query->result_array() as $row)
					$jt_recruiter_ids[] = $row["jt_recruiter_id"];

				// When we have multiple processes running, avoid
				// having them chase each other by randomizing the order
				shuffle($jt_recruiter_ids);
			}
			if ($verbose)
				echo "Updating recruiter materialized views for " . count($jt_recruiter_ids) . " recruiter IDs:\n";
	
			$this->updateRecruiterViews($cutoffTimestamp, $jt_recruiter_ids, $incremental, $master_conn_id, $max_procs, $verbose);
		}
	}

	private function connectToMasterDB() {
		global $webvars;
		$master_conn_id = @mysqli_connect($webvars["DB_HOST"],
				$webvars["DB_USER"],
				$webvars["DB_PWD"],
				$webvars["DB_MAIN"]);
		if (!$master_conn_id) {
			log_message("error", "Unable to connect to master DB: (" . mysqli_connect_errno() . ") " . mysqli_connect_error());
			return false;
		}
		return $master_conn_id;
	}

	private function connectAndUnregisterRunningJobById($running_job_id) {
		//
		// If the functions take too long, our connection could time out, so reconnect each time.
		//
		$master_conn_id = $this->connectToMasterDB();
		if ($master_conn_id === false)
			return false;

		if (!unregisterRunningJobById($master_conn_id, $running_job_id))
			return false;

		mysqli_close($master_conn_id);

		return true;
	}


	private function updateRecruiterViews($cutoffTimestamp, $jt_recruiter_ids, $incremental, $master_conn_id, $max_procs, $verbose) {
		$this->load->model("ViewBuilder");
		$this->load->helper("mysql");

		$lockExpiration = $this->config->item("MaterializedViewLockExpiration");
		$minLifetime = $this->config->item("MaterializedViewMinLifetime");
		$minLifetimefortimetrend = $this->config->item("MaterializedViewMinLifetimeForTimeTrend");

		if ($max_procs > 0) {
			$job_expiration_in_minutes = $this->config->item("MaterializedViewRecruiterProcessesTimeout");
			expireRunningJobs($master_conn_id, __FUNCTION__, $job_expiration_in_minutes, $verbose);

			$running_job_id = registerRunningJob($master_conn_id, gethostname(),
					__FUNCTION__, getmypid(), $max_procs, $verbose);
			if (!$running_job_id)
				return false;
		} else {
			$running_job_id = false;
		}

		if ($verbose)
			echo "Checking for expired locks...\n";
		$expired_locks = $this->ViewBuilder->expireLocks("rs_advertisement_stats", $lockExpiration);
		if ($expired_locks > 0 && $verbose)
			echo "Removed $expired_locks advertisement lock(s) older than $lockExpiration minutes.\n";
		$expired_locks = $this->ViewBuilder->expireLocks("rs_campaign_stats", $lockExpiration);
		if ($expired_locks > 0 && $verbose)
			echo "Removed $expired_locks campaign lock(s) older than $lockExpiration minutes.\n";
		$expired_locks = $this->ViewBuilder->expireLocks("rs_recruiter_source_stats", $lockExpiration);
		if ($expired_locks > 0 && $verbose)
			echo "Removed $expired_locks rs_recruiter_source_stats lock(s) older than $lockExpiration minutes.\n";
		$expired_locks = $this->ViewBuilder->expireLocks("rs_recruiter_source_time_trend", $lockExpiration);
		if ($expired_locks > 0 && $verbose)
			echo "Removed $expired_locks recruiter_source_time_trend lock(s) older than $lockExpiration minutes.\n";
		if ($verbose)
			echo "DONE checking expired locks.\n";

		foreach ($jt_recruiter_ids as $jt_recruiter_id) {
			$timer_start = microtime(true);

			if ($this->ViewBuilder->updateAdvertisement($cutoffTimestamp, $incremental, $jt_recruiter_id, $verbose, $running_job_id) === false)
			{
				echo "Error updating advertisement stats tables for recruiter ID $jt_recruiter_id.  Please see CI log.\n";
				break;
			}
				
			if ($this->ViewBuilder->updateCampaign($cutoffTimestamp, $incremental, $jt_recruiter_id, $verbose, $running_job_id) === false)
			{
				echo "Error updating campaign stats tables for recruiter ID $jt_recruiter_id.  Please see CI log.\n";
				break;
			}

			if ($this->ViewBuilder->updateRecruiterSource($minLifetime, $cutoffTimestamp, $incremental, $jt_recruiter_id, $verbose, $running_job_id) === false)
			{
				echo "Error updating recruiter source stats table for recruiter ID $jt_recruiter_id.  Please see CI log.\n";
				break;
			}
				
			if ($this->ViewBuilder->updateRecruiterSourceTimeTrend($minLifetimefortimetrend, $cutoffTimestamp, $jt_recruiter_id, $verbose, $running_job_id) === false)
			{
				echo "Error updating recruiter source time trend table for recruiter ID $jt_recruiter_id.  Please see CI log.\n";
				break;
			}

			$microtimeDiff = microtime(true) - $timer_start;
			$logMessage = "ViewBuilderCron->" . __FUNCTION__ . "() executed in " . $microtimeDiff . " seconds for recruiter $jt_recruiter_id";

			if ($verbose)
				echo $logMessage;

			$this->myutils->send2syslog($logMessage);
				
			if ($verbose)
				echo ".";
		}
		if ($verbose)
			echo "DONE\n";

		if ($running_job_id)
			$this->connectAndUnregisterRunningJobById($running_job_id);
	}

	private function updateNetworkViews($cutoffTimestamp, $verbose, $master_conn_id) {
		$this->load->model("ViewBuilder");
		$this->load->helper("mysql");

		$lockExpiration = $this->config->item("MaterializedViewLockExpiration");
		$minLifetime = $this->config->item("MaterializedViewMinLifetime");
		$max_procs = $this->config->item("MaterializedViewMaxRecruiterProcesses");

		if ($max_procs > 0) {
			$job_expiration_in_minutes = $this->config->item("MaterializedViewNetworkProcessesTimeout");
			expireRunningJobs($master_conn_id, __FUNCTION__, $job_expiration_in_minutes, $verbose);

			$running_job_id = registerRunningJob($master_conn_id, gethostname(),
					__FUNCTION__, getmypid(), $max_procs, $verbose);
			if (!$running_job_id)
				return false;
		} else {
			$running_job_id = false;
		}

		if ($verbose)
			echo "Updating network materialized views:\n";

		if ($verbose)
			echo " - source stats...";
		if ($this->ViewBuilder->updateSource($minLifetime, $lockExpiration, $cutoffTimestamp, $verbose, $running_job_id) === false)
			echo "Error updating source stats table.  Please see CI log.\n";
		else if ($verbose)
			echo "DONE\n";

		$atsStatsMinLifetime = $this->config->item("MaterializedViewAtsStatsMinLifetime");

		if ($verbose)
			echo " - ATS stats by month...";
		if ($this->ViewBuilder->updateAtsStatsByMonth($atsStatsMinLifetime, $lockExpiration, $cutoffTimestamp, $verbose, $running_job_id) === false)
			echo "Error updating ats stats by month table.  Please see CI log.\n";
		else if ($verbose)
			echo "DONE\n";

		if ($verbose)
			echo " - Employer ATS stats by day...";
		if ($this->ViewBuilder->updateEmployerAtsStatsByDay($atsStatsMinLifetime, $lockExpiration, $cutoffTimestamp, $verbose, $running_job_id) === false)
			echo "Error updating employer ats stats by day table.  Please see CI log.\n";
		else if ($verbose)
			echo "DONE\n";

		if ($verbose)
			echo " - Employer ATS stats by browser...";
		if ($this->ViewBuilder->updateEmployerAtsStatsByBrowser($atsStatsMinLifetime, $lockExpiration, $cutoffTimestamp, $verbose, $running_job_id) === false)
			echo "Error updating employer ats stats by browser table.  Please see CI log.\n";
		else if ($verbose)
			echo "DONE\n";

		if ($verbose)
			echo " - Employer ATS stats by hour...";
		if ($this->ViewBuilder->updateEmployerAtsStatsByHour($atsStatsMinLifetime, $lockExpiration, $cutoffTimestamp, $verbose, $running_job_id) === false)
			echo "Error updating employer ats stats by hour table.  Please see CI log.\n";
		else if ($verbose)
			echo "DONE\n";

		if ($verbose)
			echo " - Employer ATS stats by month...";
		if ($this->ViewBuilder->updateEmployerAtsStatsByMonth($atsStatsMinLifetime, $lockExpiration, $cutoffTimestamp, $verbose, $running_job_id) === false)
			echo "Error updating employer ats stats by month table.  Please see CI log.\n";
		else if ($verbose)
			echo "DONE\n";

		if ($verbose)
			echo " - Employer stats by job function...";
		if ($this->ViewBuilder->updateEmployerStatsByJobFunction($atsStatsMinLifetime, $lockExpiration, $cutoffTimestamp, $verbose, $running_job_id) === false)
			echo "Error updating employer stats by job function table.  Please see CI log.\n";
		else if ($verbose)
			echo "DONE\n";

		if ($running_job_id)
			$this->connectAndUnregisterRunningJobById($running_job_id);
	}

}
