<?php
/**
 * Breakfast menu - data and renderer.
 *
 * The lunch menu lives as hand-written markup inside an Elementor HTML widget
 * on page 21, which makes a price change a hunt through 46KB of HTML. The
 * breakfast menu is kept here instead: structured data in the theme, rendered
 * into the same markup the stylesheet already dresses. Editing a price means
 * editing one line in this file, and it travels with a theme-only deploy.
 *
 * Source: the client's "BREAKFAST OPEN 9:00 AM" menu card.
 *
 * @package BabaYagaChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The breakfast menu.
 *
 * Each group is: heading, an optional shared price, an optional note, and the
 * items. An item is array( name, price, description ); a description may be
 * an empty string for items the menu lists without one.
 *
 * @return array
 */
function babayaga_child_breakfast_menu() {
	return array(
		array(
			'heading' => 'Breakfast Platters',
			'note'    => 'All breakfast platters are served with our skillet potatoes. Your choice of toast or tortillas.',
			'items'   => array(
				array( 'The Traditional', '$16.95', 'Two eggs any style with bacon or sausage.' ),
				array( 'Tri-Tip &amp; Eggs', '$21.95', 'Two eggs cooked your way, served with tri-tip steak.' ),
				array( 'Cajun Scramble', '$18.95', 'Spicy Louisiana hot links with scrambled eggs, saut&eacute;ed onions, and peppers.' ),
				array( 'Tavern Breakfast', '$17.95', 'Two eggs any style with bacon, sausage, and ham.' ),
				array( 'Egg Sandwich', '$17.95', 'Bacon, lettuce, tomato, cheese, and avocado on a brioche bun with chipotle aioli.' ),
				array( 'Breakfast Burrito', '$17.95', 'Scrambled eggs, ham, bacon, and potato with mixed Jack and cheddar cheese and fresh salsa, wrapped in a flour tortilla.' ),
				array( 'Mexican Scramble', '$18.95', 'Two eggs scrambled with pulled pork, saut&eacute;ed jalape&ntilde;os, onions, tomatoes, and cilantro. Served with corn tortillas.' ),
				array( 'Huevos Rancheros', '$19.95', 'Two eggs your style served over corn tortillas, topped with queso ranchero and homemade salsa.' ),
				array( 'Chilaquiles', '$20.95', 'Tortilla chips tossed with homemade salsa, topped with tender chicken, mixed cheese, and two fried eggs.' ),
				array( 'French Toast', '$16.95', 'Brioche filled with amaretto cream cheese, dredged in frosted flakes and seared golden brown. Presented with fresh whipped cream and seasonal berries.' ),
				array( 'Buttermilk Pancakes', '$15.95', '' ),
			),
		),
		array(
			'heading' => 'Tavern Omelettes',
			'note'    => 'All breakfast omelettes are served with our ranch skillet potatoes. Your choice of toast or tortillas.',
			'items'   => array(
				array( 'Cheese', '$16.95', 'A mix of Jack and cheddar.' ),
				array( 'Ham &amp; Cheese', '$15.99', 'Finely diced lean ham and cheddar cheese.' ),
				array( 'Spinach', '$17.95', 'Fresh spinach with Jack cheese.' ),
				array( 'Denver', '$17.95', 'Grilled ham, onion, and bell pepper.' ),
				array( 'Chili Cheese', '$18.95', 'Omelette with saut&eacute;ed tomatoes and onion, cheddar cheese, topped with our award-winning chili.' ),
				array( 'Veggie', '$17.95', 'Saut&eacute;ed zucchini, mushrooms, tomatoes, cheese, and spinach.' ),
				array( 'Mexican Omelette', '$18.95', 'Mixed cheese topped with sour cream, guacamole, and homemade salsa.' ),
				array( 'Chorizo &amp; Eggs', '$18.95', 'Two eggs scrambled with chorizo, onion, and cilantro. Sour cream and fresh salsa.' ),
			),
		),
		array(
			'heading' => 'Breakfast Sides',
			'items'   => array(
				array( 'Sliced Tomatoes', '$3.95', '' ),
				array( 'Skillet Potatoes', '$4.99', '' ),
				array( 'Toast', '$2.55', 'White, wheat, rye, or sourdough.' ),
				array( 'Sliced Ham', '$4.95', '' ),
				array( 'Sausage Link', '$4.95', '' ),
				array( 'Sliced Bacon', '$4.95', '' ),
				array( 'Bowl of Fruit', '$9.95', '' ),
			),
		),
	);
}

/**
 * Render one menu item in the markup the stylesheet expects.
 *
 * @param array $item name, price, description.
 * @return string
 */
function babayaga_child_render_item( $item ) {
	list( $name, $price, $desc ) = $item;

	$html  = '<div class="t101-item">';
	$html .= '<div class="t101-item-row">';
	$html .= '<p class="t101-name">' . $name . '</p>';

	if ( '' !== $price ) {
		$html .= '<span class="t101-price">' . $price . '</span>';
	}

	$html .= '</div>';

	if ( '' !== $desc ) {
		$html .= '<p class="t101-desc">' . $desc . '</p>';
	}

	return $html . '</div>';
}

/**
 * The breakfast section, as a tab body for the menu page.
 *
 * @param bool $active Whether this tab should open first.
 * @return string
 */
function babayaga_child_breakfast_section( $active = false ) {
	$html  = '<div class="t101-section' . ( $active ? ' active' : '' ) . '" id="t101-breakfast">';
	$html .= '<div class="t101-sec-head"><h2>Breakfast</h2>';
	$html .= '<div class="t101-sec-sub">Served from 9:00 am</div></div>';

	foreach ( babayaga_child_breakfast_menu() as $group ) {
		$html .= '<div class="t101-group-head"><h3>' . $group['heading'] . '</h3></div>';

		if ( ! empty( $group['note'] ) ) {
			$html .= '<p class="t101-note">' . $group['note'] . '</p>';
		}

		$html .= '<div class="t101-grid">';

		foreach ( $group['items'] as $item ) {
			$html .= babayaga_child_render_item( $item );
		}

		$html .= '</div>';
	}

	return $html . '</div>';
}
