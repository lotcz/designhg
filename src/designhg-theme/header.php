<?php
/**
 * Header file
 *
 * @package designhg-theme
 * @since 1.0
 */
?>
<!doctype html>
<html class="no-js" <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo('charset'); ?>">
		<meta http-equiv="x-ua-compatible" content="ie=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php wp_head(); ?>

		<!-- Global site tag (gtag.js) - Google Analytics -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=UA-125647525-1"></script>
		<script>
		  window.dataLayer = window.dataLayer || [];
		  function gtag(){dataLayer.push(arguments);}
		  gtag('js', new Date());

		  gtag('config', 'UA-125647525-1');
		</script>
	</head>

  <body <?php body_class(); ?>>

  <?php
  $ad = dhg_adboard_get_current();
  $adboardStyle = "";
  $adboardScript = "";
  if ($ad) {
	  $img = dhg_adboard_get_image_url($ad->ID);
	  $adboardStyle = "style=\"background-image: url('$img');\"";
	  $link = dhg_adboard_get_link_url($ad->ID);
	  if ($link) {
		  $adboardScript = "onclick=\"window.open('$link', '_blank');return false;\"";
		  ?>
		  <script>
			  window.addEventListener(
				  'load',
				  () => {
					  const insideElements = document.querySelectorAll(
						  '#wrapper .container, #wrapper .toggle-block-container, #wrapper .tt-footer, #content-wrapper > .visible'
					  );
					  insideElements.forEach(
						  (el) => {
							  el.addEventListener('click', (e) => e.stopPropagation());
						  }
					  )
				  }
			  );
		  </script>
		  <?php
	  }
  }
  ?>

  <div id="wrapper" <?php echo $adboardStyle?> <?php echo $adboardScript?>>

  <?php magplus_sideheader(); ?>
  <?php search_popup(); ?>
  <?php magplus_popup(); ?>

  <div id="content-wrapper">
  <?php magplus_header_template(magplus_get_opt('header-template')); ?>
  <?php get_template_part('templates/title-wrapper/default'); ?>
