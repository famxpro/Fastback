<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class analytics_model extends MY_Model {
	public function __construct(){
		parent::__construct();
	}

	public function get_stats($ids = ""){

		$result = $this->db->select("a.*")
				->from("instagram_analytics_stats as a")
				->join("instagram_accounts as b", "a.account = b.id")
				->where("b.ids = '{$ids}'")
				->order_by("a.date","desc")
				->limit(15,0)
				->get()
				->result();

		$userinfo = $this->db->select("a.*")
				->from("instagram_analytics as a")
				->join("instagram_accounts as b", "a.account = b.id")
				->where("b.ids = '{$ids}'")
				->get()
				->row();
		
		$result_tmp = array_reverse($result);	
		$list = array();
		$followers_tmp = -1;
		$following_tmp = -1;
		$posts_tmp = -1;
		
		$followers_value_string = "";
		$following_value_string = "";
		$engagement_value_string = "";
		$date_string = "";
		$count_date = 0;

		if(!empty($result_tmp)){
			foreach ($result_tmp as $key => $row) {
				//List summary
				$data = json_decode($row->data);
				
				$follower_count = $data->follower_count;
				$following_count = $data->following_count;
				$engagement = $data->engagement;
				$media_count = $data->media_count;

				$list[$row->date] = (object)array(
					"followers" => $follower_count,
					"following" => $following_count,
					"posts" => $media_count,
					"followers_sumary" => ($followers_tmp == -1)?"":($follower_count-$followers_tmp),
					"following_sumary" => ($following_tmp == -1)?"":($following_count-$following_tmp),
					"posts_sumary" => ($posts_tmp == -1)?"":($media_count-$posts_tmp),
					"date" => $row->date
				);

				$followers_tmp = $follower_count;
				$following_tmp = $following_count;
				$posts_tmp = $media_count;

				//Followers chart
				$followers_value_string .= "{$follower_count},";

				//Followers chart
				$following_value_string .= "{$following_count},";

				//Followers chart
				$engagement_value_string .= "{$engagement},";

				//Date chart
				$date_string .= "'{$row->date}',";
			}

			//Cound Date
			$start_date = strtotime($result_tmp[0]->date);
			$end_date = strtotime($result_tmp[count($result_tmp) - 1]->date);
			$datediff = $end_date - $start_date;
			$count_date = round($datediff / (60 * 60 * 24));

			$followers_value_string = "[".substr($followers_value_string, 0, -1)."]";
			$following_value_string = "[".substr($following_value_string, 0, -1)."]";
			$engagement_value_string = "[".substr($engagement_value_string, 0, -1)."]";
			$date_string  = "[".substr($date_string, 0, -1)."]";

			//Following chart
			$result = (object)array(
				"list_summary" => $list,
				"followers_chart" => $followers_value_string,
				"following_chart" => $following_value_string,
				"engagement_chart" => $engagement_value_string,
				"date_chart" => $date_string,
				"data" => isset($userinfo->data)?json_decode($userinfo->data):"",
				"total_days" => $count_date
			);

			return $result;
		}

		return false;
	}


}
