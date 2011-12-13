<footer id="footer" role="complementary">	
	<div class="foot">
	<?php if(has_action('custom_footer')): ?>
		<?php do_action('custom_footer'); ?>
		<?php wp_footer(); ?>
	<?php else: ?>
		<span><?php _e('Powered by ', 'digressit'); ?> <a href="http://digress.it/?v=<?php echo DIGRESSIT_VERSION; ?>"><b>Digress.it</b></a></span>
		<?php wp_footer(); ?>
		
	<?php endif; ?>
	</div>
</footer>

<div id="debug-message"></div>

</div> <!-- wrapper -->

<?php get_lightboxes(); ?>

<?php 
    // Print script elements containing content, not src value, that need to be placed at the end of the body.
    if (has_action('custom_final_scripts')) {
        do_action('custom_final_scripts');
    }
?>

</body>
</html> 