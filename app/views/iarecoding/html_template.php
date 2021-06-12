<!DOCTYPE html>
<html>
	<head>
		<!-- html title -->
		<title>
			<?php echo $this->escapeHtml( $html_title ); ?>
		</title>
		
		<!-- favicon -->
		<link rel="shortcut icon" href="<?php echo BASE_URL_ASSETS; ?>images/favicon.ico" />

		<!-- meta -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

		<!-- css from outside -->
		<link rel="preconnect" href="https://fonts.gstatic.com">
		<link href="https://fonts.googleapis.com/css2?family=Ubuntu" rel="stylesheet">

		<!-- global js from outisde -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
		
		<!-- page specific css -->
		<link href="<?php echo BASE_URL_ASSETS; ?>css/iarecoding/global.css" rel="stylesheet" type="text/css">
		<link href="<?php echo BASE_URL_ASSETS; ?>css/iarecoding/pc.css" media='screen and (min-width: 1400px)' rel="stylesheet" type="text/css">
		<link href="<?php echo BASE_URL_ASSETS; ?>css/iarecoding/mobile.css" media='screen and (max-width: 1400px)' rel="stylesheet" type="text/css">

		<!-- page specific js -->
		<script src="<?php echo BASE_URL_ASSETS; ?>js/iarecoding/global.js"></script>
	</head>
	<body>
		<div class="site-container">	
			<img src="<?php echo BASE_URL_ASSETS; ?>images/logo50x50.png" />
			<h1>
				<!-- title from our model -->
				<?php echo $this->escapeHtml( $page['info']['title'] ); ?>
			</h1>
			<div class="section-container">
				<h3>
					<!-- sub title from our model -->
					<?php echo $this->escapeHtml( $page['info']['subtitle'] ); ?>
				</h3>
			</div>
			<!-- description html snippet -->
			<?php echo $description_html; ?>
			<?php foreach ( $page['links'] as $link ) : // loop over links we got from our model and display them! ?>
				<div class="section-container">
					<a href="<?php echo $this->escapeHtml( $link['link_url'] ); ?>" target="_blank">
						<?php echo $this->escapeHtml( $link['link_title'] ); ?>
					</a>
				</div>
			<?php endforeach; ?>
		</div>
	</body>
</html>