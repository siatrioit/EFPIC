<?php
/**
 * efpic Block efpic/collections-list render template
 *
 * @param array    $attributes Block attributes passed from the editor
 * @param string   $content    Block content (unused for this block)
 * @param WP_Block $block      Block instance
 */

$wrapper = get_block_wrapper_attributes( array( 'class' => 'efpic-collections-list-block' ) );
echo '<div ' . $wrapper . '>';
efpic_the_collections_list( $attributes );
echo '</div>';
