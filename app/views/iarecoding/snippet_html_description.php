<div class="section-container">
	<div class="info-text-container">
		<div class="info-text-heading-bar">
			<!-- title from our model -->
			<?php echo $this->escapeHtml( $page['info']['title'] ); ?>
			<div class="info-text-toggle">
				hide
			</div>
		</div>
		<div class="info-text">
			<!-- info text from our model -->
			<?php echo $this->escapeHtml( $page['info']['description'] ); ?>
		</div>
	</div>
</div>