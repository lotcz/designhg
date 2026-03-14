<?php
/**
 * Header file
 *
 * @package magplus
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

  <div id="wrapper">

  <?php magplus_sideheader(); ?>
  <?php search_popup(); ?>
  <?php magplus_popup(); ?>

  <div id="content-wrapper">
  <?php magplus_header_template(magplus_get_opt('header-template')); ?>
  <?php get_template_part('templates/title-wrapper/default'); ?>
