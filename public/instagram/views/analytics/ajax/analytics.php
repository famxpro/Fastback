<?php 
if(!empty($result)){
$account_data = $result->data;
$top_hashtags = $account_data->top_hashtags;
$top_mentions = $account_data->top_mentions;
$userinfo = $account_data->userinfo;
$feeds = $account_data->feeds;
$follower_count = $userinfo->follower_count;
$media_count = $userinfo->media_count;
$total_days = $result->total_days;
?>

<div class="ig-analytics" id="box-analytics">
	<div class="container">
		<div class="userinfo">
			<div class="avatar">
				<img src="<?=$userinfo->profile_pic_url?>">
			</div>
			<div class="infos">
				<div class="name"><?=$userinfo->username?></div>
				<ul class="sumary">
					<li><span><?=number_format($userinfo->media_count)?></span> posts</li>
					<li><span><?=number_format($userinfo->follower_count)?></span> followers</li>
					<li><span><?=number_format($userinfo->following_count)?></span> following</li>
				</ul>		

				<div class="fullname"><?=$userinfo->full_name?></div>
				<div class="description"><?=$userinfo->biography?></div>
				<div class="website"><a href="<?=$userinfo->external_url?>" target="_blank"><?=$userinfo->external_url?></a></div>

			</div>
		</div>
		<ul class="box-sumary">
			<li>
				<div>
					<span><?=$account_data->engagement?>%</span><?=lang("Engagement")?>
					<i class="activity-option-help webuiPopover fa fa-question-circle" data-content="<?=lang("The engagement rate is the number of active likes / comments on each post")?>" data-delay-show="300" data-title="<?=lang("Engagement")?>" data-target="webuiPopover1"></i>
				</div>
			</li>
			<li>
				<div>
					<span><?=$account_data->average_likes?></span><?=lang("Average Likes")?>
					<i class="activity-option-help webuiPopover fa fa-question-circle" data-content="<?=lang("Average likes based on the last 10 posts")?>" data-delay-show="300" data-title="<?=lang("Average Likes")?>" data-target="webuiPopover1"></i>
				</div>
			</li>
			<li>
				<div>
					<span><?=$account_data->average_comments?></span><?=lang("Average Comments")?>
					<i class="activity-option-help webuiPopover fa fa-question-circle" data-content="<?=lang("Average comments based on the last 10 posts")?>" data-delay-show="300" data-title="<?=lang("Average Comments")?>" data-target="webuiPopover1"></i>
				</div>
			</li>
			<span class="clearfix"></span>
		</ul>
		<div class="box-analytics">
			<div class="box-head">
				<h3 class="title"><?=lang("Profile Growth & Discovery")?></h3>
				<div class="description"><?=lang("See insights on how your profile has grown and changed over time.")?></div>
			</div>

			<div class="row">
	        	<div class="col-md-12">
					<div class="card-body box-analytic mb0">
			            <canvas id="ig-analytics-followers-line-stacked-area" height="300"></canvas>
			        </div>
			    </div>
			</div>
	        <div class="title-chart"><?=lang("Followers evolution chart")?></div>

	        <div class="row">
	        	<div class="col-md-12">
	        		<div class="card-body box-analytic mb0">
			            <canvas id="ig-analytics-following-line-stacked-area" height="300"></canvas>
			        </div>
	        	</div>
	        </div>	
	        <div class="title-chart"><?=lang("Following evolution chart")?></div>

	        <div class="box-head">
				<h3 class="title"><?=lang("Account Stats Summary")?></h3>
				<div class="description"><?=lang("Showing last 15 entries.")?></div>
			</div>
			<div class="table_sumary">
				<?php
				$total_followers_summany = 0;
				$total_following_summany = 0;
				$total_posts_summany = 0;
				$compare_new_followers_value_string = "";
				$compare_new_following_value_string = "";
				$compare_total_followers_value_string = "";
				$compare_total_following_value_string = "";
				?>

				<table class="table">
					<thead>
						<tr>
							<td>Date</td>
							<td colspan="2"><?=lang("Followes")?></td>
							<td colspan="2"><?=lang("Following")?></td>
							<td colspan="2"><?=lang("Posts")?></td>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($result->list_summary as $key => $row){
							$followers_status = "text-default";
							$followers_sumary = "-";
							$total_followers_summany += (int)$row->followers_sumary;
							if($row->followers_sumary > 0){
								$followers_sumary = "+".$row->followers_sumary;
								$followers_status = "text-success";
							}else if($row->followers_sumary < 0){
								$followers_sumary = $row->followers_sumary;
								$followers_status = "text-danger";
							}

							$following_status = "text-default";
							$following_sumary = "-";
							$total_following_summany += (int)$row->following_sumary;
							if($row->following_sumary > 0){
								$following_sumary = "+".$row->following_sumary;
								$following_status = "text-success";
							}else if($row->following_sumary < 0){
								$following_sumary = $row->following_sumary;
								$following_status = "text-danger";
							}

							$posts_status = "text-default";
							$posts_sumary = "-";
							$total_posts_summany += (int)$row->posts_sumary;
							if($row->posts_sumary > 0){
								$posts_sumary = "+".$row->posts_sumary;
								$posts_status = "text-success";
							}else if($row->posts_sumary < 0){
								$posts_sumary = $row->posts_sumary;
								$posts_status = "text-danger";
							}

							$compare_new_followers_value_string .= (int)$followers_sumary.",";
							$compare_new_following_value_string .= (int)$following_sumary.",";
							$compare_total_followers_value_string .= (int)$row->followers.",";
							$compare_total_following_value_string .= (int)$row->following.",";
						?>
						<tr>
							<td><?=date("D, d M, Y", strtotime($row->date))?></td>
							<td><?=$row->followers?></td>
							<td><span class="<?=$followers_status?>"><?=$followers_sumary?></span></td>
							<td><?=$row->following?></td>
							<td><span class="<?=$following_status?>"><?=$following_sumary?></span></td>
							<td><?=$row->posts?></td>
							<td><span class="<?=$posts_sumary?>"><?=$posts_sumary?></span></td>
						</tr>
						<?php }?>
					</tbody>
					<tfoot>
						<?php 
						$total_followers_status = "text-default";
						if($total_followers_summany > 0){
							$total_followers_summany = "+".$total_followers_summany;
							$total_followers_status = "text-success";
						}else if($total_followers_summany < 0){
							$total_followers_status = "text-danger";
						}

						$total_following_status = "text-default";
						if($total_following_summany > 0){
							$total_following_summany = "+".$total_following_summany;
							$total_following_status = "text-success";
						}else if($total_following_summany < 0){
							$total_following_status = "text-danger";
						}

						$total_posts_status = "text-default";
						if($total_posts_summany > 0){
							$total_posts_summany = "+".$total_posts_summany;
							$total_posts_status = "text-success";
						}else if($total_posts_summany < 0){
							$total_posts_status = "text-danger";
						}
						?>

						<tr>
							<td><i class="ft-crosshair"></i> <?=lang("Total Summary")?></td>
							<td colspan="2"><span class="<?=$total_followers_status?>"><?=($total_followers_summany!=0)?$total_followers_summany:"-"?></span></td>
							<td colspan="2"><span class="<?=$total_following_status?>"><?=($total_following_summany!=0)?$total_following_summany:"-"?></span></td>
							<td colspan="2"><span class="<?=$total_posts_status?>"><?=($total_posts_summany!=0)?$total_posts_summany:"-"?></span></td>
						</tr>
					</tfoot>
				</table>
			</div>

			<?php 
			$compare_new_followers_value_string = "[".substr($compare_new_followers_value_string, 0, -1)."]";
			$compare_new_following_value_string = "[".substr($compare_new_following_value_string, 0, -1)."]";
			$compare_total_followers_value_string = "[".substr($compare_total_followers_value_string, 0, -1)."]";
			$compare_total_following_value_string = "[".substr($compare_total_following_value_string, 0, -1)."]";
			?>

			<div class="row">
				<div class="col-md-6">
					<div class="card-body box-analytic mb0">
			            <canvas id="ig-analytics-get-followers-following-line-stacked-area" height="300"></canvas>
			        </div>
			        <div class="title-chart"><?=lang("Compare new Followers and Following evolution chart")?></div>
				</div>
				<div class="col-md-6">
					<div class="card-body box-analytic mb0">
			            <canvas id="ig-analytics-total-followers-following-line-stacked-area" height="300"></canvas>
			        </div>
			        <div class="title-chart"><?=lang("Compare total Followers and Following evolution chart")?></div>
				</div>
			</div>	

			<div class="box-head">
				<h3 class="title"><?=lang("Average Engagement Rate")?></h3>
				<div class="description"><?=lang("Each value in this chart is equal to the Average Engagement Rate of the account in that specific day.")?></div>
			</div>
			<div class="row">
	        	<div class="col-md-12">
					<div class="card-body box-analytic mb0">
			            <canvas id="ig-analytics-engagement-line-stacked-area" height="300"></canvas>
			        </div>
			    </div>
			</div>
	        <div class="title-chart"><?=lang("Average Engagement Rate Chart")?></div>

	        <div class="box-head">
				<h3 class="title"><?=lang("Future Projections")?></h3>
				<div class="description"><?=lang("Here you can see the approximated future projections based on your previous days averages")?></div>
			</div>

			<?php 
			$average_followers = $total_days>0?(int)ceil($total_followers_summany/$total_days):0;
			$average_posts = $total_days>0?(int)ceil($total_posts_summany/$total_days):0;
			?>
			<div class="table_sumary">
				<table class="table">
					<thead>
						<tr>
							<td><?=lang("Time Until")?></td>
							<td><?=lang("Date")?></td>
							<td><?=lang("Followes")?></td>
							<td><?=lang("Posts")?></td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><?=lang("Current Stats")?></td>
							<td><?=date("d M, Y", strtotime(reset($result->list_summary)->date))?></td>
							<td><?=number_format(reset($result->list_summary)->followers)?></td>
							<td><?=number_format(reset($result->list_summary)->posts)?></td>
						</tr>
						<?php if($total_days > 0){ ?>
						<tr>
		                    <td>30 <?=lang("days")?></td>
		                    <td><?= (new \DateTime())->modify('+30 day')->format('Y-m-d') ?></td>
		                    <td><?= number_format($follower_count + ($average_followers * 30)) ?></td>
		                    <td><?= number_format($media_count + ($average_posts * 30)) ?></td>
		                </tr>
		                <tr>
		                    <td>60 <?=lang("days")?></td>
		                    <td><?= (new \DateTime())->modify('+60 day')->format('Y-m-d') ?></td>
		                    <td><?= number_format($follower_count + ($average_followers * 60)) ?></td>
		                    <td><?= number_format($media_count + ($average_posts * 60)) ?></td>
		                </tr>
		                <tr>
		                    <td>3 <?=lang("months")?></td>
		                    <td><?= (new \DateTime())->modify('+90 day')->format('Y-m-d') ?></td>
		                    <td><?= number_format($follower_count + ($average_followers * 90)) ?></td>
		                    <td><?= number_format($media_count + ($average_posts * 90)) ?></td>
		                </tr>
		                <tr>
		                    <td>6 <?=lang("months")?></td>
		                    <td><?= (new \DateTime())->modify('+180 day')->format('Y-m-d') ?></td>
		                    <td><?= number_format($follower_count + ($average_followers * 180)) ?></td>
		                    <td><?= number_format($media_count + ($average_posts * 180)) ?></td>
		                </tr>
		                <tr>
		                    <td>9 <?=lang("months")?></td>
		                    <td><?= (new \DateTime())->modify('+279 day')->format('Y-m-d') ?></td>
		                    <td><?= number_format($follower_count + ($average_followers * 279)) ?></td>
		                    <td><?= number_format($media_count + ($average_posts * 279)) ?></td>
		                </tr>
		                <tr>
		                    <td>1 <?=lang("year")?></td>
		                    <td><?= (new \DateTime())->modify('+365 day')->format('Y-m-d') ?></td>
		                    <td><?= number_format($follower_count + ($average_followers * 365)) ?></td>
		                    <td><?= number_format($media_count + ($average_posts * 365)) ?></td>
		                </tr>
		                <tr>
		                    <td>1 <?=lang("year and half")?></td>
		                    <td><?= (new \DateTime())->modify('+547 day')->format('Y-m-d') ?></td>
		                    <td><?= number_format($follower_count + ($average_followers * 547)) ?></td>
		                    <td><?= number_format($media_count + ($average_posts * 547)) ?></td>
		                </tr>
		                <tr>
		                    <td>2 <?=lang("years")?></td>
		                    <td><?= (new \DateTime())->modify('+730 day')->format('Y-m-d') ?></td>
		                    <td><?= number_format($follower_count + ($average_followers * 730)) ?></td>
		                    <td><?= number_format($media_count + ($average_posts * 730)) ?></td>
		                </tr>
		                <?php }?>
					</tbody>
					<tfoot>
						<?php if($total_days > 0){ ?>
						<tr>

							<?php 
							$average_followers = "-";
							if($average_followers > 0){
								$average_followers = "<span class='text-success'>+".number_format($average_followers)."<span>";
							}else if($average_followers < 0){
								$average_followers = "<span class='text-danger'>".number_format($average_followers)."<span>";
							}

							$average_posts = "-";
							if($average_posts > 0){
								$average_posts = "<span class='text-success'>+".number_format($average_posts)."<span>";
							}else if($average_posts < 0){
								$average_posts = "<span class='text-danger'>".number_format($average_posts)."<span>";
							}
							?>

		                    <td colspan="2"><i class="ft-crosshair"></i> <?=lang("Based on an average of")?></td>
		                    <td><?= sprintf(lang("%s followers/day"), $average_followers) ?></td>
		                    <td><?= sprintf(lang("%s posts/day"), $average_posts) ?></td>
		                </tr>
						<?php }else{?>
						<tr>
							<td colspan="4" style="font-weight: 400"><?=lang("There is not enough data to generate future projections, please come back tomorrow.")?></td>
						</tr>
						<?php }?>
					</tfoot>
				</table>
			</div>

	        <div class="box-head none-export">
				<h3 class="title"><?=lang("Top Posts")?></h3>
				<div class="description"><?=lang("Top posts from the last 10 posts")?></div>
			</div>

			<div class="row none-export">
				<div class="owl-carousel">
					<?php if(!empty($feeds)){
					foreach ($feeds as $key => $row) {
						$row = (object)$row;
					?>
				  	<div class="item">
				  		<?=InstagramHelper::get_embed_html($row->media_id)?>
					</div>
					<?php }}?>
				</div>
			</div>

			<div class="row">
				<?php if(!empty($top_mentions)){?>
				<div class="col-md-6">
					<div class="box-head">
						<h3 class="title"><?=lang("Top mentions")?></h3>
						<div class="description"><?=lang("Top mentions from the last 10 posts")?></div>

					</div>
					<ul class="summary-list-group">
						<?php 
						$count = 1;
						foreach ($top_mentions as $key => $value) {
						?>
						<li class="item"><div class="num"><?=$count?></div> <a href="https://www.instagram.com/<?=$key?>" target="_blank">@<?=$key?></a> (<span><?=$value?></span>)</li>
						<?php $count++; }?>
					</ul>
				</div>
				<?php }?>
				<?php if(!empty($top_hashtags)){?>
				<div class="col-md-6">
					<div class="box-head">
						<h3 class="title"><?=lang("Top hashtags")?></h3>
						<div class="description"><?=lang("Top hashtags from the last 10 posts")?></div>

					</div>
					<ul class="summary-list-group">
						<?php 
						$count = 1;
						foreach ($top_hashtags as $key => $value) {
						?>
						<li class="item"><div class="num"><?=$count?></div> <a href="https://www.instagram.com/explore/tags/<?=$key?>" target="_blank">#<?=$key?></a> (<span><?=$value?></span>)</li>
						<?php $count++; }?>
					</ul>
				</div>
				<?php }?>
			</div>	
		</div>
	</div>
</div>

<?php }else{?>
	<div class="ig-analytics">
		<div class="dataTables_empty"></div>
		<div class="ig-analytics-empty-notice">
			<div class="title"><?=lang("No data")?></div>
			<div class="description"><?=lang("We could not retrieve data from your Account, Try to re-login and try to again")?></div>
			<?php if($account->status == 0){?>
			<a href="#" class="btn btn-primary"><?=lang("Re-login")?></a>
			<?php }?>
		</div>
	</div>
<?php }?>

<script type="text/javascript">
	$(document).ready(function(){
		<?php if(!empty($result)){?>
		Instagram_analytics.lineChart(
            "ig-analytics-followers-line-stacked-area",
            <?=$result->date_chart?>, 
            [
                <?=$result->followers_chart?>,
            ],
            [
                "<?=lang('Followers')?>"
            ],
            "line",
            ["rgba(255,0,94,1)"]
        );

        Instagram_analytics.lineChart(
            "ig-analytics-following-line-stacked-area",
            <?=$result->date_chart?>, 
            [
                <?=$result->following_chart?>,
            ],
            [
                "<?=lang('Following')?>"
            ],
            "line",
            ["rgba(33,150,243,1)"]
        );

        Instagram_analytics.lineChart(
            "ig-analytics-get-followers-following-line-stacked-area",
            <?=$result->date_chart?>, 
            [
                <?=$compare_new_followers_value_string!="-"?$compare_new_followers_value_string:"[]"?>,
                <?=$compare_new_following_value_string!="-"?$compare_new_following_value_string:"[]"?>,
            ],
            [
                "<?=lang('Followers')?>",
                "<?=lang('Following')?>"
            ],
            "line",
            ["rgba(255,0,94,1)", "rgba(33,150,243,1)"]
        );

        Instagram_analytics.lineChart(
            "ig-analytics-total-followers-following-line-stacked-area",
            <?=$result->date_chart?>, 
            [
                <?=$compare_total_followers_value_string?>,
                <?=$compare_total_following_value_string?>
            ],
            [
                "<?=lang('Followers')?>",
                "<?=lang('Following')?>"
            ],
            "line",
            ["rgba(255,0,94,1)", "rgba(33,150,243,1)"]
        );

        Instagram_analytics.lineChart(
            "ig-analytics-engagement-line-stacked-area",
            <?=$result->date_chart?>, 
            [
                <?=$result->engagement_chart?>,
            ],
            [
                "<?=lang("Average Engagement Rate")?>"
            ],
            "line",
            ["rgba(64,212,29,1)"]
        );
        <?php }?>
	  	$(".owl-carousel").owlCarousel({
	  		nav: true,
	  		responsiveClass:true,
    		responsive:{
	        	0:{
	            	items:1
	        	},
		        768:{
		            items:3
		        }
		    }		
	  	});
	});

	if ( typeof window.instgrm !== 'undefined' ) {
	    window.instgrm.Embeds.process();
	}
</script>

<style type="text/css">
	.ig-analytics{
		border-top: 1px solid #f5f5f5;
		padding-top: 103px;
	}

	.ig-analytics .userinfo{
		position: relative;
		margin-bottom: 30px;
		min-height: 151px;
	}

	.ig-analytics .userinfo .avatar{
		position: absolute;
		left: 0;
	}

	.ig-analytics .userinfo .avatar img{ 
		border-radius: 100px;
	}

	.ig-analytics .userinfo .infos{
		margin-left: 250px;
	}

	.ig-analytics .userinfo .infos .name{
		font-size: 28px;
	    line-height: 32px;
	    margin: -5px 0 -6px;
	    display: block;
	    overflow: hidden;
	    text-overflow: ellipsis;
	    white-space: nowrap;
	    color: #262626;
	    font-weight: 300;
	}

	.ig-analytics .userinfo .infos .sumary{
		margin: 20px 0;
	}

	.ig-analytics .userinfo .infos .sumary li{
		display: inline-block;
		padding-right: 40px;
		font-size: 16px;
	}


	.ig-analytics .userinfo .infos .sumary li span{
		font-weight: bold;
	}


	.ig-analytics .userinfo .infos .fullname{
		font-weight: bold;
		margin-bottom: 5px;
	}

	.ig-analytics .userinfo .infos .description{
		margin-bottom: 5px;
		color: #696969;
	}

	.ig-analytics .userinfo .infos .website a{
		color: #003569;
	    text-decoration: none;
	    font-weight: 600;
	    display: block;
	    overflow: hidden;
	    text-overflow: ellipsis;
	    white-space: nowrap;
	}

	.ig-analytics .title-chart{
		margin: 10px 15px 25px;
		text-align: center;
		font-weight: 600;
		color: #0087ff;
	}

	.ig-analytics .box-sumary{
		border-left: 1px solid #f5f5f5;
		border-top: 1px solid #f5f5f5;
		border-bottom: 1px solid #f5f5f5;
	}

	.ig-analytics .box-sumary li{
		float: left;
		width: 33.333333%;
		position: relative;
		margin: 0;
		font-size: 12px;
		text-transform: uppercase;
		color: #7b8994;
	}

	.ig-analytics .box-sumary li div{
		border-right: 1px solid #f5f5f5;
		padding: 15px;
	}

	.ig-analytics .box-sumary li span{
		display: block;
		font-size: 20px;
		font-weight: 600;
		text-transform: inherit;
		color: #000;
	}

	.ig-analytics .box-head{
		margin-top: 40px;
		margin-bottom: 30px;
	}

	.ig-analytics .table_sumary table{
		margin-top: 20px;
	}

	.ig-analytics .table_sumary table .text-success{
		color: #71d473;
	}

	.ig-analytics .table_sumary table .text-danger{
		color: #e64945;
	}

	.ig-analytics .table_sumary table thead{
		background: #333;
		color: #fff;
		font-weight: bold;
	}

	.ig-analytics .table_sumary table tfoot{
		background: #f7f7f7;
		font-weight: bold;
	}

	.ig-analytics .table_sumary table td{
		padding: 15px 8px!important;
	}

	.ig-analytics .owl-carousel .item{
		margin: 0px 15px;
	}

	.ig-analytics .owl-nav button{
		font-size: 130px!important;
	    color: #ff4c8e!important;
	    position: absolute;
	    top: calc(50% - 111px);
	}

	.ig-analytics .owl-nav button.owl-prev{
		left: 20px;
	}

	.ig-analytics .owl-nav button.owl-next{
		right: 20px;
	}
	.ig-analytics .summary-list-group{
	    font-size: 16px;
	    color: #a0a0a0;
	}

	.ig-analytics .summary-list-group a{
		color: #a0a0a0;
	}

	.ig-analytics .summary-list-group a:hover{
		text-decoration: underline;
		color: #ff4c8e;
	}

	.ig-analytics .summary-list-group span{
		color: #36a3f7;
	}

	.ig-analytics .summary-list-group .num{
		display: inline-block;
		width: 40px;
		height: 40px;
		color: #ff4c8e;
		border: 1px solid #ffefef;
		margin-bottom: 15px;
		text-align: center;
		font-size: 22px;
		margin-right: 10px;
	}

	.ig-analytics-empty-notice{
		text-align: center;
	}

	.ig-analytics-empty-notice .title{
		font-size: 20px;
		color: #2196f3;
		text-transform: uppercase;
	    font-weight: bold;
	}

	.ig-analytics-empty-notice .description{
		font-size: 16px;
		padding: 10px;
	}

	.ig-analytics-empty-notice .btn{
		border-radius: 100px;
	}
	
	@media (max-width: 768px){
		.ig-analytics .userinfo .infos {
		    margin-left: 165px;
		}
	}
</style>