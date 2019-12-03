<div class="wrap-content container module">
	<div class="users app-table">
			<div class="row">
				<div class="col-md-12 mb15">
					<h3 class="pull-left m0" style="color: #636363"><?=lang("Modules & Plugins")?></h3>
					<a href="<?=PATH?>module/popup_install" class="btn btn-primary pull-right ajaxModal border-circle" title=""><span class="ft-plus-circle"></span> <?=lang('install')?></a>
					<div class="clearfix"></div>
				</div>
			</div>
			<?php if(!empty($result)){?>
			<div class="row">

				<?php foreach ($result as $key => $row) {  
				$category = explode($row->category, "/");
				?>
				<div class="col-md-4">
					<div class="item">
						<div class="box-img">
							<a href="<?=$row->link?>">
								<img src="<?=$row->banner?>">
							</a>
							<div class="box-action">
								
							</div>
						</div>
						<div class="box-info">
							<h2 class="title">
								<a href="<?=$row->link?>" title="<?=$row->name?>"><?=$row->name?></a>
							</h2>
							<div class="category">Version <?=$row->version?></div>
							<div class="box-price">
								<div class="price">
									<div class="default">$<?=number_format($row->price_regular, 2)?></div>
								</div>
								<div class="pull-right">
								<?php if(!empty($purchases)){?>
				  					<?php 
				  					$check_purchase = false;
				  					$check_version = false;
				  					foreach ($purchases as $purchase) {?>

				  						
				  						<?php if($purchase->pid == $row->pid){?>

					  						<?php 
					  						$check_purchase = true;
					  						$script_version = $row->version;
											$current_version = $purchase->version;

											//check required php version
											$version_compare = version_compare($script_version, $current_version);
											if ($version_compare > 0) {
											    $check_version = true;
											}
											?>

					  						<?php if($check_version){?>

											<div class="btn-group">
							  					<a href="<?=PATH?>module/upgrade/<?=$purchase->purchase_code?>/<?=$current_version?>" data-redirect="<?=PATH?>module" class="btn border-circle btn-danger pull-right actionItem" title=""><span class="ft-arrow-up"></span> <?=lang('upgrade')?></a>
							  				</div>

											<?php }else{?>

											<a href="javascript:void(0);" class="btn btn-primary border-circle"><?=lang('purchased')?></a>

											<?php }?>

				  						<?php }?>

				  					<?php }?>
				  					
			  						<?php if(!$check_purchase){?>
			  						<a href="https://stackposts.com/payment/<?=$row->ids?>" class="btn btn-buy"><?=lang("Buy now")?></a>
			  						<?php }?>

				  				<?php }else{?>
				  				<a href="https://stackposts.com/payment/<?=$row->ids?>" class="btn btn-buy"><?=lang("Buy now")?></a>
				  				<?php }?>
								</div>
							</div>
							<div class="clearfix"></div>
						</div>
					</div>
				</div>
				<?php }?>
			</div>
			<?php }?>
		</div>
	</div>
</div>