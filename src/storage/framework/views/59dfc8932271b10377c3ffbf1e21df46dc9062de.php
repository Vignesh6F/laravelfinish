<?php $__env->startSection('main'); ?>
<div class="" ng-controller="user">
	<div class="topbanner">
		<div class="container">
			<div class="col-lg-12 displayflex">
				<div class="col-lg-6 col-md-6">
					<div class="topbannertxt">
						<h1><?php echo e(trans('messages.home.title')); ?></h1>

						<p><?php echo e(trans('messages.home.desc')); ?></p>

						<ul>
							<li>
								<?php if($app_links[0]->value !="" ): ?>
								<a href="<?php echo e($app_links[0]->value); ?>" target="_blank"><img src="images/new/app.png" alt="app"></a>
								<?php endif; ?>
							</li>
							<li>
								<?php if($app_links[2]->value !="" ): ?>
									<a href="<?php echo e($app_links[2]->value); ?>" target="_blank">
										<img src="<?php echo e(url('images/new/google.png')); ?>" alt="Get it on Googleplay" class="CToWUd bot_footimg">
									</a>
								<?php endif; ?>
							</li>
						</ul>
					</div>
				</div>
				<div class="col-lg-6 col-md-6">
					<div class="topbannerimg">
						<img src="images/new/topbanner.png" alt="banner">
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="alllogin">
		<div class="container">
			<?php if(Auth::user()==null): ?>
			<div class="col-lg-12 alllogintop">
				<?php echo e(Form::open(array('url' => 'driver_register','class' => ''))); ?>

					<?php if(Auth::user()==null): ?>
						<div class="col-lg-4">
							<div class="allloginone">
								<h3><?php echo e(trans('messages.user.ride_with')); ?> <?php echo e($site_name); ?></h3>

								<a href="<?php echo e(url('signup_rider')); ?>"><?php echo e(trans('messages.home.siginup')); ?> <img src="images/new/arrow-right.svg" alt="arrow"></a>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="allloginone">
								<h3><?php echo e(trans('messages.home.siginup_drive')); ?></h3>

								<a href="<?php echo e(url('signup_driver')); ?>"><?php echo e(trans('messages.home.siginup')); ?> <img src="images/new/arrow-right.svg" alt="arrow"></a>
							</div>
						</div>
					<?php endif; ?>
					<?php if(Auth::guard('company')->user()==null && Auth::user()==null): ?>
					<?php endif; ?>
					<?php if(Auth::guard('company')->user()==null): ?>
						<div class="col-lg-4">
							<div class="allloginone">
								<h3><?php echo e(trans('messages.home.siginup_company')); ?></h3>

								<a href="<?php echo e(url('signup_company')); ?>"><?php echo e(trans('messages.home.siginup')); ?> <img src="images/new/arrow-right.svg" alt="arrow"></a>
							</div>
						</div>
					<?php endif; ?>
					
				<?php echo e(Form::close()); ?>

			</div>
			<?php endif; ?>
			<div class="col-lg-12 allloginbottom">
				<div class="col-lg-4">
					<div class="alllogintwo">
						<img src="images/new/easyway.svg" alt="icon">
						<h4><?php echo e(trans('messages.home.easy_way')); ?></h4>
						<p><?php echo e(trans('messages.home.easy_content')); ?></p>
					</div>
				</div>
				<div class="col-lg-4">
					<div class="alllogintwo">
						<img src="images/new/anywhare.svg" alt="icon">
						<h4><?php echo e(trans('messages.home.anywhere')); ?></h4>
						<p><?php echo e(trans('messages.home.anywhere_content')); ?></p>
					</div>
				</div>
				<div class="col-lg-4">
					<div class="alllogintwo">
						<img src="images/new/lowcost.svg" alt="icon">
						<h4><?php echo e(trans('messages.home.lowcost')); ?></h4>
						<p><?php echo e(trans('messages.home.lowcost_content')); ?></p>
					</div>
				</div>
			</div>

			<div class="col-lg-12 allloginbottom1">
				<a href="<?php echo e(url('ride')); ?>"><?php echo e(trans('messages.home.reason')); ?> <img src="images/new/arrow-right.svg" alt="arrow"></a>
			</div>
		</div>
	</div>

	<div class="cls_sectionone">
		<div class="container">
			<div class="row displayflex">
				<div class="col-lg-6">
					<div class="cls_sectiononeimg">
						<img src="images/new/image3.jpg" alt="banner">
					</div>
				</div>
				<div class="col-lg-6">
					<div class="cls_sectiononetxt">
						<h4 class="text-twotruncate"><?php echo e(trans('messages.home.drive_you')); ?> <?php echo e(trans('messages.home.you_need')); ?></h4>
						<p class="text-threetruncate"><?php echo e(trans('messages.home.drive_with')); ?> <?php echo e($site_name); ?><?php echo e(trans('messages.home.goals')); ?></p>
					</div>
				</div>
			</div>
			<div class="row cls_sectionbtm displayflex">
				<div class="col-lg-6">
					<div class="cls_sectiononetxt">
						<h4 class="text-twotruncate"><?php echo e(trans('messages.home.drive_you1')); ?></h4>
						<p class="text-threetruncate"><?php echo e(trans('messages.home.goals1')); ?></p>
					</div>
				</div>
				<div class="col-lg-6">
					<div class="cls_sectiononeimg">
						<img src="images/new/image2.png" alt="banner">
					</div>
					</div>
				
			</div>
		</div>
	</div>

	<div class="cls_arriving">
		<div class="container">
			<div class="title">
				<h5><?php echo e(trans('messages.home.now_arrive')); ?></h5>
				<h6><?php echo e(trans('messages.home.safe')); ?></h6>
			</div>
			<div class="row">
				<div class="col-lg-6">
					<div class="cls_arrivingin">
						<img src="images/new/arrive2.svg" alt="banner">

						<h5><?php echo e(trans('messages.home.helping')); ?></h5>
						<p><?php echo e(trans('messages.home.city_with')); ?> <?php echo e($site_name); ?> <?php echo e(trans('messages.home.city_with_content')); ?></p>
					</div>
				</div>

				<div class="col-lg-6">
					<div class="cls_arrivingin">
						<img src="images/new/arrive1.svg" alt="banner">

						<h5><?php echo e(trans('messages.home.safe_ride')); ?></h5>
						<p><?php echo e(trans('messages.home.backseat')); ?> <?php echo e($site_name); ?><?php echo e(trans('messages.home.designed')); ?></p>
					</div>
				</div>

			</div>
		</div>
	</div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('templatesign', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /opt/lampp/htdocs/client/gofer_2.5/resources/views/home/home.blade.php ENDPATH**/ ?>