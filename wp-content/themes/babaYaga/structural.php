<?php
/**
 * Tavern 101 - structural layer.
 *
 * Research into current restaurant sites is consistent on three things this
 * build was missing outright, none of which a stylesheet can supply:
 *
 *   1. The hero carries a headline and primary actions over the photograph.
 *      This one showed a cartoon badge and nothing else - no heading, no CTA.
 *   2. Ordering is reachable from anywhere, not only from the header.
 *   3. Food photography does the selling. This build ran vintage desert stock
 *      art on the pages that should have been showing the food.
 *
 * Implementation note
 * -------------------
 * Elementor's image widget bypasses `elementor/widget/render_content` (only
 * text-based widgets pass through it), and its container render actions did
 * not fire reliably for this template either. Rather than fight the internals,
 * the two markup changes are applied to the finished page in one output
 * filter. It is server-side, so the H1 and links are in the HTML source for
 * search engines and screen readers, and deleting this file returns every
 * page to exactly what it was. No database row, Elementor document or plugin
 * setting is modified.
 *
 * @package BabaYagaChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The primary call to action, used by the hero and the sticky order bar.
 *
 * SpotOn is the ordering platform: it takes an actual takeout order. inKind
 * is a different product - prepaid dining credit that guests redeem in the
 * restaurant - so it does NOT replace ordering. If the CTA is ever pointed at
 * inKind, change the label with it, or the button promises something the
 * destination cannot do.
 *
 * @return array url, label and whether it opens in a new tab.
 */
function babayaga_child_order_cta() {
	return array(
		'url'      => babayaga_child_order_url_value(),
		'label'    => 'Order online',
		'external' => true,
	);
}

/**
 * The single ordering destination for the whole site.
 *
 * Uber Eats, on the client's instruction: every order button points here, so
 * it is defined once and the header, hero and sticky bar all read from it.
 * The previous SpotOn link is rewritten to this at render time, because that
 * one lives in an Elementor button in the database.
 *
 * Changing this one string moves every order button on the site.
 *
 * @return string
 */
function babayaga_child_order_url_value() {
	return 'https://www.ubereats.com/store/tavern-101/GYazcyRaVx27GyGxQ6oM6A';
}

/**
 * Secondary calls to action. Any entry with an empty url is not rendered.
 *
 * Ordering commission is the reason these sit BESIDE the primary button
 * rather than replacing it. A SpotOn order is direct and the restaurant
 * keeps it; a DoorDash order carries a marketplace commission. Pointing the
 * main button at DoorDash would push customers who were happy to order
 * direct through the paid channel, so direct ordering stays primary and the
 * marketplace is offered as the delivery option.
 *
 * Uber Eats is the confirmed delivery partner (store link supplied by the
 * client). The tracking query string their link arrived with is dropped - it
 * belongs to the referrer that generated it, not to us.
 *
 * @return array
 */
function babayaga_child_secondary_ctas() {
	return array(
		array(
			'url'   => '', // Ordering is Uber Eats now, which is the primary button.
			'label' => 'Order delivery',
		),
		array(
			'url'   => '', // inKind venue page, e.g. https://inkind.com/<slug>.
			'label' => 'Gift cards &middot; 20% back',
		),
	);
}

/**
 * Back-compat helper: the ordering URL on its own.
 *
 * @return string
 */
function babayaga_child_order_url() {
	$cta = babayaga_child_order_cta();

	return $cta['url'];
}

/**
 * Vintage stock art mapped to the restaurant's own photography.
 *
 * Keys are filename fragments of the black-and-white desert scenery that was
 * carrying the home and about pages. Values are food and venue photography
 * that was already sitting in the media library, unused.
 *
 * @return array
 */
function babayaga_child_photo_map() {
	return array(
		'slide1'                        => array(
			'2026/09/101-grill-classic-cheeseburger-side.jpg',
			'Classic cheeseburger with a side of fries at 101 Grill',
		),
		'Gemini_Generated_Image_itdcjx' => array(
			'2026/09/101-grill-baby-back-ribs.jpg',
			'Half rack of slow-smoked baby back ribs',
		),
		'Gemini_Generated_Image_kknu80' => array(
			'2026/09/101-grill-brisket-sandwich-side.jpg',
			'Smoked brisket sandwich with a side',
		),
		'Gemini_Generated_Image_in5axw' => array(
			'2026/09/101-grill-salmon-salad.jpg',
			'Cajun salmon salad over mixed greens',
		),
		'Gemini_Generated_Image_wbjg2m' => array(
			'2026/09/101-grill-buffalo-wings.jpg',
			'Jumbo buffalo wings with blue cheese, carrots and celery',
		),
	);
}

/**
 * The hero content block.
 *
 * Wording comes from the restaurant's own assets - the logo mark reads
 * "Smoking Daily" and "Since 1928" - rather than invented marketing claims.
 *
 * @return string
 */
function babayaga_child_hero_markup() {
	$cta  = babayaga_child_order_cta();
	$menu = esc_url( home_url( '/menu/' ) );

	$target = ! empty( $cta['external'] ) ? ' target="_blank" rel="noopener"' : '';

	$html  = '<div class="t101-hero">';
	$html .= '<p class="t101-hero-eyebrow">Agoura Hills, California</p>';
	$html .= '<h1 class="t101-hero-title">101 Grill</h1>';
	$html .= '<p class="t101-hero-sub">Slow-smoked. Laid-back. Local.</p>';
	$html .= '<div class="t101-hero-actions">';
	$html .= '<a class="t101-cta t101-cta--solid" href="' . esc_url( $cta['url'] ) . '"' . $target . '>' . $cta['label'] . '</a>';
	$html .= '<a class="t101-cta t101-cta--ghost" href="' . $menu . '">View the menu</a>';

	foreach ( babayaga_child_secondary_ctas() as $extra ) {
		if ( empty( $extra['url'] ) ) {
			continue;
		}
		$html .= '<a class="t101-cta t101-cta--ghost" href="' . esc_url( $extra['url'] ) . '" target="_blank" rel="noopener">' . $extra['label'] . '</a>';
	}

	$html .= '</div></div>';

	return $html;
}

/**
 * The printable lunch menu.
 *
 * The client's 2026 V8 PDF, recompressed from 6.7MB to 1.4MB (the artwork was
 * embedded at 4310px for a letter-size page). It ships inside the theme rather
 * than the media library so it travels with a theme-only deploy.
 *
 * @return string Absolute URL, or '' when the file is not present.
 */
function babayaga_child_menu_pdf_url() {
	$rel = '/assets/doc/101-grill-lunch-menu.pdf';

	if ( ! file_exists( get_stylesheet_directory() . $rel ) ) {
		return '';
	}

	return get_stylesheet_directory_uri() . $rel;
}

/**
 * Offer the PDF beneath the menu masthead.
 *
 * Guests who want the whole menu on one screen - or to print it - are not
 * served by tabbed sections, and restaurants are routinely asked for a
 * printable menu. The on-page menu stays the primary experience.
 *
 * @param string $html Complete page HTML.
 * @return string
 */
function babayaga_child_inject_menu_pdf( $html ) {
	// t101-menu is also used as a styling wrapper on Hours & Location and on
	// the homepage's teaser card, and a lunch-menu download makes no sense on
	// either. The tab nav only exists on the real menu page.
	if ( false === strpos( $html, 'id="t101MainNav"' ) || false !== strpos( $html, 't101-menu-download' ) ) {
		return $html;
	}

	$url = babayaga_child_menu_pdf_url();

	if ( '' === $url ) {
		return $html;
	}

	$block  = '<div class="t101-menu-download">';
	$block .= '<a class="t101-cta t101-cta--ghost" href="' . esc_url( $url ) . '" download>';
	$block .= 'Download the lunch menu <span>PDF &middot; 1.4&nbsp;MB</span>';
	$block .= '</a></div>';

	return preg_replace( '/(<header class="t101-header">.*?<\/header>)/is', '${1}' . $block, $html, 1 );
}

/**
 * Rewrite one img tag if it points at mapped stock art.
 *
 * The whole tag is rebuilt so a stale srcset cannot keep serving the old file
 * at responsive breakpoints.
 *
 * @param string $tag  The img tag.
 * @param string $base Uploads base URL.
 * @return string
 */
function babayaga_child_rewrite_img( $tag, $base ) {
	foreach ( babayaga_child_photo_map() as $needle => $data ) {
		if ( false === strpos( $tag, $needle ) ) {
			continue;
		}

		list( $file, $alt ) = $data;

		$tag = preg_replace( '/\s(?:srcset|sizes)="[^"]*"/i', '', $tag );
		$tag = preg_replace( '/(\ssrc=")[^"]*(")/i', '${1}' . esc_url( $base . $file ) . '${2}', $tag, 1 );

		if ( preg_match( '/\salt="/i', $tag ) ) {
			$tag = preg_replace( '/(\salt=")[^"]*(")/i', '${1}' . esc_attr( $alt ) . '${2}', $tag, 1 );
		} else {
			$tag = preg_replace( '/<img\b/i', '<img alt="' . esc_attr( $alt ) . '"', $tag, 1 );
		}

		break;
	}

	return $tag;
}

/**
 * Photography that may stand in for a repeated shot, in preference order.
 *
 * @return array Filename stems inside the uploads folder.
 */
function babayaga_child_photo_pool() {
	return array(
		'tri-tip-sandwich-side',
		'bbq-chopped-salad',
		'mac-and-cheese',
		'salmon-dinner',
		'buffalo-wings',
		'pulled-pork-sandwich-side',
		'chocolate-lava-cake',
		'salmon-salad',
		'onion-rings',
		'beet-salad',
		'bread-pudding',
		'cornbread',
	);
}

/**
 * Stop the same dish appearing several times on one page.
 *
 * The catering and private-events bands were each built from whatever image
 * was nearest to hand, so the homepage served two photographs across nine
 * slots while thirty-six commissioned shots sat unused in the library. The
 * first use of any photograph is always left exactly as the page author set
 * it; only a repeat is swapped, and only for a shot not already on the page.
 *
 * This is deliberately keyed on repetition rather than on position, so it
 * survives Elementor edits: give a section its own photograph in the editor
 * and this pass simply stops firing for it.
 *
 * @param string $html Complete page HTML.
 * @param string $base Uploads base URL.
 * @return string
 */
function babayaga_child_dedupe_photos( $html, $base ) {
	$seen = array();
	$dir  = trailingslashit( wp_get_upload_dir()['basedir'] );

	return preg_replace_callback(
		'/<img\b[^>]*>/i',
		static function ( $m ) use ( &$seen, $base, $dir ) {
			$tag = $m[0];

			// Only the commissioned food photography, never logos or line art.
			if ( ! preg_match( '#src="[^"]*?/(\d{4}/\d{2})/101-grill-([a-z0-9-]+?)(-\d+x\d+)?\.jpg"#i', $tag, $p ) ) {
				return $tag;
			}

			list( , $ym, $stem, $size ) = $p + array( 3 => '' );

			if ( ! isset( $seen[ $stem ] ) ) {
				$seen[ $stem ] = true;

				return $tag;
			}

			foreach ( babayaga_child_photo_pool() as $candidate ) {
				if ( isset( $seen[ $candidate ] ) ) {
					continue;
				}

				$rel = $ym . '/101-grill-' . $candidate . $size . '.jpg';

				if ( ! file_exists( $dir . $rel ) ) {
					$rel = $ym . '/101-grill-' . $candidate . '.jpg';

					if ( ! file_exists( $dir . $rel ) ) {
						continue;
					}
				}

				$seen[ $candidate ] = true;

				$alt = ucfirst( str_replace( array( '-side', '-' ), array( ' with a side', ' ' ), $candidate ) ) . ' at 101 Grill';
				$tag = preg_replace( '/\s(?:srcset|sizes)="[^"]*"/i', '', $tag );
				$tag = preg_replace( '/(\ssrc=")[^"]*(")/i', '${1}' . esc_url( $base . $rel ) . '${2}', $tag, 1 );

				if ( preg_match( '/\salt="/i', $tag ) ) {
					$tag = preg_replace( '/(\salt=")[^"]*(")/i', '${1}' . esc_attr( $alt ) . '${2}', $tag, 1 );
				}

				return $tag;
			}

			return $tag;
		},
		$html
	);
}

/**
 * Give the food photography a srcset.
 *
 * Every dish shot was being served at its full 1800px master into an 800px
 * slot - roughly 250KB each, nine of them on the homepage - because the
 * markup carries a bare src and no srcset at all. WordPress already generated
 * 768 / 1024 / 1536 renditions on upload; this advertises them so the browser
 * can take the one that fits, and retina screens still get a large file.
 *
 * Only images that have no srcset of their own are touched.
 *
 * @param string $html Complete page HTML.
 * @param string $base Uploads base URL.
 * @return string
 */
function babayaga_child_add_srcset( $html, $base ) {
	$dir = trailingslashit( wp_get_upload_dir()['basedir'] );

	return preg_replace_callback(
		'/<img\b[^>]*>/i',
		static function ( $m ) use ( $base, $dir ) {
			$tag = $m[0];

			if ( preg_match( '/\ssrcset=/i', $tag ) ) {
				return $tag;
			}

			if ( ! preg_match( '#src="[^"]*?/(\d{4}/\d{2})/(101-grill-[a-z0-9-]+)\.jpg"#i', $tag, $p ) ) {
				return $tag;
			}

			list( , $ym, $stem ) = $p;

			$candidates = glob( $dir . $ym . '/' . $stem . '-*x*.jpg' );

			if ( ! $candidates ) {
				return $tag;
			}

			$parts = array();

			foreach ( $candidates as $file ) {
				if ( ! preg_match( '/-(\d+)x(\d+)\.jpg$/', $file, $d ) ) {
					continue;
				}

				$w = (int) $d[1];

				// Square crops are a different picture, not a smaller one.
				if ( $w < 600 || $w === (int) $d[2] ) {
					continue;
				}

				$parts[ $w ] = esc_url( $base . $ym . '/' . basename( $file ) ) . ' ' . $w . 'w';
			}

			if ( ! $parts ) {
				return $tag;
			}

			$size = @getimagesize( $dir . $ym . '/' . $stem . '.jpg' );

			if ( $size ) {
				$parts[ (int) $size[0] ] = esc_url( $base . $ym . '/' . $stem . '.jpg' ) . ' ' . (int) $size[0] . 'w';
			}

			ksort( $parts );

			$slot = preg_match( '/\swidth="(\d+)"/i', $tag, $w ) ? (int) $w[1] : 800;
			$sizes = sprintf( '(max-width: %1$dpx) 100vw, %1$dpx', $slot );

			return preg_replace(
				'/<img\b/i',
				'<img srcset="' . implode( ', ', $parts ) . '" sizes="' . esc_attr( $sizes ) . '"',
				$tag,
				1
			);
		},
		$html
	);
}

/**
 * The menu, assembled from the theme.
 *
 * Lunch comes from inc/menu-lunch.html and breakfast from the data in
 * inc/menu-breakfast.php, so both are edited in the theme and travel with a
 * theme-only deploy. Nothing here is built with preg_replace: menu prices are
 * full of "$16.95", and in a replacement string "$16" is a backreference.
 *
 * @return string Markup, or '' if the lunch file is missing.
 */
function babayaga_child_menu_markup() {
	$file = get_stylesheet_directory() . '/inc/menu-lunch.html';

	if ( ! is_readable( $file ) ) {
		return '';
	}

	$html = (string) file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions -- local theme file.

	if ( '' === trim( $html ) ) {
		return '';
	}

	// Menu photography ships with the theme, so the path is resolved here
	// rather than hard-coded into the HTML file.
	$html = str_replace( '{{THEME}}', get_stylesheet_directory_uri(), $html );

	if ( ! function_exists( 'babayaga_child_breakfast_section' ) ) {
		return $html;
	}

	$nav = strpos( $html, '<nav class="t101-nav"' );
	$end = false === $nav ? false : strpos( $html, '>', $nav );

	if ( false === $end ) {
		return $html;
	}

	$button = '<button type="button" data-target="t101-breakfast">Breakfast</button>';
	$html   = substr_replace( $html, $button, $end + 1, 0 );

	$close = strpos( $html, '</nav>' );

	if ( false === $close ) {
		return $html;
	}

	return substr_replace( $html, babayaga_child_breakfast_section(), $close + 6, 0 );
}

/**
 * Serve the theme's menu in place of the one stored in the page.
 *
 * The menu is a single Elementor HTML widget holding 46KB of hand-written
 * markup, which makes a price change a hunt through a database blob. The
 * theme copy is authoritative; this swaps it in at render time so the page
 * shows current prices without anyone editing Elementor.
 *
 * It stands down on its own: once the theme markup has been pasted into the
 * widget - the breakfast section is the marker - the page is already current
 * and this does nothing.
 *
 * @param string $html Complete page HTML.
 * @return string
 */
function babayaga_child_swap_menu( $html ) {
	// Only the real tabbed menu. The homepage has a small t101-menu card of
	// its own with no nav, and that is a homepage design decision, not a
	// stale copy of the menu - it is left alone.
	if ( false === strpos( $html, 'id="t101MainNav"' ) ) {
		return $html;
	}

	$start = strpos( $html, '<section class="t101-menu">' );

	if ( false === $start || false !== strpos( $html, 'id="t101-breakfast"' ) ) {
		return $html;
	}

	$close = strpos( $html, '</section>', $start );

	if ( false === $close ) {
		return $html;
	}

	$markup = babayaga_child_menu_markup();

	if ( '' === $markup ) {
		return $html;
	}

	return substr_replace( $html, $markup, $start, $close + 10 - $start );
}

/**
 * Point every remaining order link at the current ordering platform.
 *
 * The header's "Order Online" button is an Elementor button stored in the
 * database, so changing the theme alone would leave it on the old platform
 * while the hero and sticky bar moved. This rewrites any surviving SpotOn
 * link wherever it appears, so there is one ordering destination site-wide.
 *
 * @param string $html Complete page HTML.
 * @return string
 */
function babayaga_child_rewrite_order_links( $html ) {
	if ( false === stripos( $html, 'order.spoton.com' ) ) {
		return $html;
	}

	$url = babayaga_child_order_url_value();

	return preg_replace_callback(
		'#href="https?://order\.spoton\.com/[^"]*"#i',
		static function () use ( $url ) {
			return 'href="' . esc_url( $url ) . '"';
		},
		$html
	);
}

/**
 * One h1 per page.
 *
 * The homepage shipped twelve. The menu card repeats its whole masthead once
 * per tab panel, and each copy carried an h1, so "Happy Hour" and "BBQ
 * Sauces" were competing with the restaurant's name for the page's single
 * most important heading. Only one is ever visible, so this is invisible on
 * screen and plainly wrong to a search engine or a screen reader.
 *
 * The first h1 in the document is kept; later ones become h2. Pages that
 * somehow have none promote their first h2 instead. The stylesheet matches
 * both tags in these containers, so nothing changes visually.
 *
 * @param string $html Complete page HTML.
 * @return string
 */
function babayaga_child_fix_headings( $html ) {
	$count = preg_match_all( '/<h1\b/i', $html );

	if ( 1 === $count ) {
		return $html;
	}

	if ( 0 === $count ) {
		// No h1 at all: the page title is sitting in an h2.
		return preg_replace( '/<h2\b([^>]*)>(.*?)<\/h2>/is', '<h1$1>$2</h1>', $html, 1 );
	}

	$seen = 0;

	return preg_replace_callback(
		'/<h1\b([^>]*)>(.*?)<\/h1>/is',
		static function ( $m ) use ( &$seen ) {
			$seen++;

			if ( 1 === $seen ) {
				return $m[0];
			}

			return '<h2' . $m[1] . '>' . $m[2] . '</h2>';
		},
		$html
	);
}

/**
 * Close the tabnabbing hole on links that open a new tab.
 *
 * The social links in the header and footer are target="_blank" with no rel,
 * which hands the opened page a window.opener reference back to this one.
 *
 * @param string $html Complete page HTML.
 * @return string
 */
function babayaga_child_fix_external_links( $html ) {
	return preg_replace_callback(
		'/<a\b[^>]*target="_blank"[^>]*>/i',
		static function ( $m ) {
			$tag = $m[0];

			if ( preg_match( '/\srel="([^"]*)"/i', $tag, $rel ) ) {
				if ( false !== stripos( $rel[1], 'noopener' ) ) {
					return $tag;
				}

				return str_replace( $rel[0], ' rel="' . esc_attr( trim( $rel[1] . ' noopener' ) ) . '"', $tag );
			}

			return preg_replace( '/<a\b/i', '<a rel="noopener"', $tag, 1 );
		},
		$html
	);
}

/**
 * Photography for the two pages that sell food but never showed any.
 *
 * Catering and Private Events both argue that 101 Grill should feed your
 * event, and both did it in words alone - between them they carried zero
 * dish photographs while thirty-six commissioned shots sat in the library.
 *
 * The band reuses the menu's own .t101-strip / .t101-shot component rather
 * than inventing a layout, so it inherits the 4:3 aspect-ratio box, the
 * object-fit crop, lazy loading and the responsive grid already in the
 * stylesheet. Full-size sources are used on purpose: the srcset pass below
 * then advertises the 768/1024/1536 renditions and the browser takes what
 * it needs.
 *
 * Keyed to the page slug, so it appears on these two pages and nowhere else.
 *
 * @return array|null Heading and shots, or null if this is not one of them.
 */
function babayaga_child_taste_band() {
	$bands = array(
		'catering'       => array(
			'label' => 'From our kitchen',
			'title' => 'A taste of what we cater',
			'shots' => array(
				array( 'tri-tip-sandwich-side', 'Slow-smoked tri-tip, served with a side' ),
				array( 'bbq-chopped-salad', 'BBQ chopped salad' ),
				array( 'mac-and-cheese', '101 Grill mac and cheese' ),
				array( 'cornbread', 'House cornbread' ),
			),
		),
		'private-events' => array(
			'label' => 'On the table',
			'title' => 'What your guests will be eating',
			'shots' => array(
				array( 'baby-back-ribs', 'Slow-smoked baby back ribs' ),
				array( 'salmon-dinner', 'Grilled salmon dinner' ),
				array( 'mixed-vegetables', 'Seasonal mixed vegetables' ),
				array( 'chocolate-lava-cake', 'Chocolate lava cake' ),
			),
		),
	);

	$slug = get_post_field( 'post_name', get_queried_object_id() );

	return isset( $bands[ $slug ] ) ? $bands[ $slug ] : null;
}

/**
 * Append the photography band just before the footer.
 *
 * @param string $html Complete page HTML.
 * @return string
 */
function babayaga_child_inject_taste_band( $html ) {
	if ( false !== strpos( $html, 't101-taste' ) || false === strpos( $html, '</main>' ) ) {
		return $html;
	}

	$band = babayaga_child_taste_band();

	if ( null === $band ) {
		return $html;
	}

	$uploads = trailingslashit( wp_get_upload_dir()['baseurl'] );
	$shots   = '';

	foreach ( $band['shots'] as $shot ) {
		list( $stem, $caption ) = $shot;
		$file = '2026/09/101-grill-' . $stem . '.jpg';

		if ( ! file_exists( trailingslashit( wp_get_upload_dir()['basedir'] ) . $file ) ) {
			continue;
		}

		$shots .= '<figure class="t101-shot">';
		$shots .= '<img src="' . esc_url( $uploads . $file ) . '" alt="' . esc_attr( $caption ) . '"';
		$shots .= ' loading="lazy" decoding="async" width="768" height="576">';
		$shots .= '<figcaption>' . esc_html( $caption ) . '</figcaption>';
		$shots .= '</figure>';
	}

	if ( '' === $shots ) {
		return $html;
	}

	$block  = '<section class="t101-taste"><div class="t101-taste-inner">';
	$block .= '<p class="t101-taste-label">' . esc_html( $band['label'] ) . '</p>';
	$block .= '<h2 class="t101-taste-title">' . esc_html( $band['title'] ) . '</h2>';
	$block .= '<div class="t101-strip">' . $shots . '</div>';
	$block .= '</div></section>';

	$pos = strrpos( $html, '</main>' );

	return substr_replace( $html, $block, $pos, 0 );
}

/**
 * Current opening hours.
 *
 * Supplied by the client on 2026-10-31. The old block carried two rows
 * ("Sunday - Thursday", "Friday & Saturday"); the new pattern needs four,
 * because Saturday and Sunday now open at 9:00 AM while the week opens at
 * 11:30. The rows live in an Elementor HTML widget on page 20, so they are
 * rewritten here rather than in the database.
 *
 * @return array day => hours.
 */
function babayaga_child_dining_hours() {
	return array(
		'Monday &ndash; Thursday' => '11:30 AM &ndash; 9:00 PM',
		'Friday'                  => '11:30 AM &ndash; 10:00 PM',
		'Saturday'                => '9:00 AM &ndash; 10:00 PM',
		'Sunday'                  => '9:00 AM &ndash; 9:00 PM',
	);
}

/**
 * Swap the two dining rows for the current four.
 *
 * The dining block is the first set of .t101-hours-row elements on the page;
 * the happy-hour rows follow it and are left alone.
 *
 * @param string $html Complete page HTML.
 * @return string
 */
function babayaga_child_update_hours( $html ) {
	if ( false === strpos( $html, 't101-hours-row' ) ) {
		return $html;
	}

	$rows = '';

	foreach ( babayaga_child_dining_hours() as $day => $time ) {
		$rows .= '<div class="t101-hours-row">';
		$rows .= '<span class="t101-hours-day">' . $day . '</span>';
		$rows .= '<span class="t101-hours-dots"></span>';
		$rows .= '<span class="t101-hours-time">' . $time . '</span>';
		$rows .= '</div>';
	}

	$done = 0;

	// A row's children are spans, so the first </div> closes the row. Do NOT
	// extend this pattern to a second </div>: that reaches past the row into
	// the block's own closing tag and takes the happy-hour rows with it.
	return preg_replace_callback(
		'#<div class="t101-hours-row">.*?</div>#s',
		static function ( $m ) use ( $rows, &$done ) {
			$done++;

			// The two dining rows come first and become the current four.
			// Everything after them (happy hour) is left untouched.
			if ( 1 === $done ) {
				return $rows;
			}

			return 2 === $done ? '' : $m[0];
		},
		$html,
		2
	);
}

/**
 * Point "Start Planning" at a page instead of a mailto.
 *
 * The catering button opened the visitor's mail client on a Yahoo address,
 * which the client reported as broken behaviour. Until the dedicated catering
 * enquiry form exists it goes to the contact page. The email address printed
 * elsewhere on the page stays a mailto, because there it is the content.
 *
 * @param string $html Complete page HTML.
 * @return string
 */
function babayaga_child_fix_planning_cta( $html ) {
	if ( false === strpos( $html, 'Start Planning' ) ) {
		return $html;
	}

	// The enquiry form lives on this same page, so jump to it.
	$url = '#catering-enquiry';

	return preg_replace_callback(
		'#<a\b([^>]*)href="mailto:[^"]*"([^>]*)>((?:(?!</a>).)*Start Planning(?:(?!</a>).)*)</a>#is',
		static function ( $m ) use ( $url ) {
			return '<a' . $m[1] . 'href="' . $url . '"' . $m[2] . '>' . $m[3] . '</a>';
		},
		$html
	);
}

/**
 * Put the catering enquiry form on the catering page.
 *
 * It sits after the photography band and immediately before the footer, so
 * the page runs: what we cater, what it looks like, then the form. The
 * "Start Planning" button higher up jumps to it.
 *
 * @param string $html Complete page HTML.
 * @return string
 */
function babayaga_child_inject_catering_form( $html ) {
	if ( ! function_exists( 'babayaga_child_catering_form' ) ) {
		return $html;
	}

	if ( false !== strpos( $html, 'id="catering-enquiry"' ) || false === strpos( $html, '</main>' ) ) {
		return $html;
	}

	if ( 'catering' !== get_post_field( 'post_name', get_queried_object_id() ) ) {
		return $html;
	}

	return substr_replace( $html, babayaga_child_catering_form(), strrpos( $html, '</main>' ), 0 );
}

/**
 * Take the still photography off the site.
 *
 * The client's instruction on 2026-11-05: the dish shots read as social
 * snaps rather than the professional photography they intend to commission,
 * so all of it comes down until the new set exists.
 *
 * The whole Elementor image widget is removed, not just its <img>. Leaving
 * the wrapper behind would leave its padding and its slot in the row, which
 * reads as a hole in the page rather than as a page without photographs.
 *
 * Hero background images are deliberately NOT touched here - they are a CSS
 * background behind white headline type, and removing them would leave white
 * text on a white band.
 *
 * @param string $html Complete page HTML.
 * @return string
 */
function babayaga_child_remove_dish_photos( $html ) {
	if ( false === strpos( $html, '101-grill-' ) ) {
		return $html;
	}

	$needle = 'elementor-widget-image';

	while ( true ) {
		// Find the next dish photograph.
		if ( ! preg_match( '#<img\b[^>]*101-grill-(?!logo)[^>]*>#i', $html, $m, PREG_OFFSET_CAPTURE ) ) {
			break;
		}

		$at = $m[0][1];

		// Walk back to the image widget that owns it.
		$start = strrpos( substr( $html, 0, $at ), '<div class="elementor-element' );

		if ( false === $start || false === strpos( substr( $html, $start, $at - $start ), $needle ) ) {
			// Not inside an image widget - drop just the tag so we cannot loop.
			$html = substr_replace( $html, '', $at, strlen( $m[0][0] ) );

			continue;
		}

		// Balanced end of that widget.
		$depth = 0;
		$end   = false;

		if ( preg_match_all( '#<div\b|</div>#', $html, $tags, PREG_OFFSET_CAPTURE, $start ) ) {
			foreach ( $tags[0] as $tag ) {
				$depth += ( '</div>' === $tag[0] ) ? -1 : 1;

				if ( 0 === $depth ) {
					$end = $tag[1] + strlen( $tag[0] );

					break;
				}
			}
		}

		if ( false === $end ) {
			$html = substr_replace( $html, '', $at, strlen( $m[0][0] ) );

			continue;
		}

		$html = substr_replace( $html, '', $start, $end - $start );
	}

	$html = babayaga_child_remove_galleries( $html );
	$html = babayaga_child_remove_hollow_media_widgets( $html );

	return babayaga_child_drop_empty_containers( $html );
}

/**
 * Remove media widgets that no longer hold any media.
 *
 * Stripping the photographs out of a carousel leaves the carousel itself:
 * a widget with no image, no text and no height, still holding its half of
 * the row open. That is what kept the hole beside the homepage copy.
 *
 * Only media widget types are considered, so a spacer or divider - which is
 * also legitimately empty - is never touched.
 *
 * @param string $html Complete page HTML.
 * @return string
 */
function babayaga_child_remove_hollow_media_widgets( $html ) {
	$types = array( 'elementor-widget-n-carousel', 'elementor-widget-image', 'elementor-widget-image-carousel', 'elementor-widget-image-box' );

	foreach ( $types as $type ) {
		$seek = 0;

		while ( false !== ( $at = strpos( $html, $type, $seek ) ) ) {
			$start = strrpos( substr( $html, 0, $at ), '<div class="elementor-element' );
			$end   = false === $start ? false : babayaga_child_div_end( $html, $start );

			if ( false === $start || false === $end ) {
				$seek = $at + 1;

				continue;
			}

			$inner = substr( $html, $start, $end - $start );

			// Anything still carrying an image or real copy stays.
			if ( preg_match( '#<(?:img|picture|source|svg|video|iframe)\b#i', $inner )
				|| '' !== trim( html_entity_decode( wp_strip_all_tags( $inner ) ) ) ) {
				$seek = $at + 1;

				continue;
			}

			$html = substr_replace( $html, '', $start, $end - $start );
			$seek = $start;
		}
	}

	return $html;
}

/**
 * The photographs for the Explore strip.
 *
 * The client's own professional set, supplied 2026-11-06. Widths are the
 * real rendered widths at 760px tall, so each <img> can declare its own
 * dimensions and the row cannot reflow as the photographs arrive.
 *
 * @return array file stem => array( width, alt )
 */
function babayaga_child_explore_shots() {
	return array(
		'breakfast-skillet'       => array( 1013, 'Breakfast skillet with eggs, peppers and toast' ),
		'blackened-chicken-salad' => array( 570, 'Blackened chicken salad with corn and peppers' ),
		'cajun-salmon-salad'      => array( 570, 'Cajun salmon over mixed greens with roasted peppers' ),
		'tri-tip-plate'           => array( 570, 'Sliced tri-tip with BBQ beans and coleslaw' ),
		'brisket-sandwich'        => array( 570, 'Smoked brisket sandwich on a brioche bun' ),
		'on-the-smoker'           => array( 570, 'Racks of ribs and chicken on the smoker' ),
		'cheeseburger'            => array( 570, 'Stacked cheeseburger with lettuce and tomato' ),
		'baby-back-ribs'          => array( 428, 'Sliced rack of slow-smoked baby back ribs' ),
		'burger-onion-rings'      => array( 1013, 'Burger served with onion rings' ),
	);
}

/**
 * The venue and service shots for the lower grid.
 *
 * Supplied 2026-11-07. All six are 4:5, so this grid keeps that ratio
 * rather than cropping square like the Explore grid above - two photo
 * grids on one page should not look like the same block twice.
 *
 * @return array file stem => array( width, alt )
 */
function babayaga_child_happening_shots() {
	return array(
		'turkey-club'        => array( 608, 'Turkey club sandwich cut into quarters' ),
		'ribs-plate'         => array( 608, 'Ribs plate with cornbread, BBQ beans and coleslaw' ),
		'ribs-plate-patio'   => array( 608, 'Ribs and sides served on the patio' ),
		'smoker-racks'       => array( 608, 'Racks of ribs and chicken in the smoker' ),
		'chopped-salad-bowl' => array( 608, 'Chopped salad being tossed in the kitchen' ),
		'street-tacos'       => array( 608, 'Street tacos with lime and salsa' ),
	);
}

/**
 * One photography grid.
 *
 * Shared by both grids so they cannot drift apart. Neither is a carousel
 * and neither has a hover overlay: both of those were tried and rejected.
 * Everything is visible at once and nothing has to be operated.
 *
 * @param string $id    Section id, also the folder under assets/img.
 * @param string $title Heading.
 * @param array  $shots Stem => array( width, alt ).
 * @param string $ratio Modifier class controlling the cell aspect.
 * @return string
 */
function babayaga_child_photo_grid( $id, $title, $shots, $ratio ) {
	$base  = get_stylesheet_directory_uri() . '/assets/img/' . $id . '/';
	$dir   = get_stylesheet_directory() . '/assets/img/' . $id . '/';
	$items = '';
	$i     = 0;

	foreach ( $shots as $stem => $meta ) {
		if ( ! file_exists( $dir . $stem . '.jpg' ) ) {
			continue;
		}

		list( $w, $alt ) = $meta;

		$items .= '<figure class="t101-explore-item" style="--i:' . (int) $i . '">';
		$items .= '<img src="' . esc_url( $base . $stem . '.jpg' ) . '"';
		$items .= ' srcset="' . esc_url( $base . $stem . '-380.jpg' ) . ' ' . (int) round( $w / 2 ) . 'w, ';
		$items .= esc_url( $base . $stem . '.jpg' ) . ' ' . (int) $w . 'w"';
		$items .= ' sizes="(max-width: 600px) 92vw, (max-width: 1000px) 46vw, 31vw"';
		$items .= ' width="' . (int) $w . '" height="760"';
		$items .= ' alt="' . esc_attr( $alt ) . '" loading="lazy" decoding="async">';
		$items .= '</figure>';
		$i++;
	}

	if ( '' === $items ) {
		return '';
	}

	$title_id = 't101-' . $id . '-title';

	$html  = '<section class="t101-explore t101-explore--' . esc_attr( $ratio ) . '" aria-labelledby="' . esc_attr( $title_id ) . '">';
	$html .= '<div class="t101-explore-inner">';
	$html .= '<h2 class="t101-explore-title" id="' . esc_attr( $title_id ) . '">' . esc_html( $title ) . '</h2>';
	$html .= '<div class="t101-explore-grid">' . $items . '</div>';
	$html .= '</div></section>';

	return $html;
}

/**
 * The upper grid, under the Explore invitation.
 *
 * @return string
 */
function babayaga_child_explore_strip() {
	return babayaga_child_photo_grid( 'explore', 'Straight from the kitchen', babayaga_child_explore_shots(), 'square' );
}

/**
 * The lower grid, before the footer.
 *
 * @param string $html Complete page HTML.
 * @return string
 */
function babayaga_child_inject_happening( $html ) {
	if ( ! is_front_page() || false !== strpos( $html, 't101-happening-title' ) || false === strpos( $html, '</main>' ) ) {
		return $html;
	}

	$grid = babayaga_child_photo_grid( 'happening', 'See what&rsquo;s happening', babayaga_child_happening_shots(), 'portrait' );

	if ( '' === $grid ) {
		return $html;
	}

	return substr_replace( $html, $grid, strrpos( $html, '</main>' ), 0 );
}

/**
 * Place the strip directly beneath the Explore 101 Grill invitation.
 *
 * @param string $html Complete page HTML.
 * @return string
 */
function babayaga_child_inject_explore_strip( $html ) {
	if ( ! is_front_page() || false !== strpos( $html, 't101-explore' ) ) {
		return $html;
	}

	$at = stripos( $html, 'EXPLORE 101 GRILL' );

	if ( false === $at ) {
		return $html;
	}

	// Walk out to the section that holds the invitation.
	$start = false;
	$seek  = 0;

	while ( true ) {
		$next = strpos( $html, '<div class="elementor-element', $seek );

		if ( false === $next || $next > $at ) {
			break;
		}

		if ( false !== strpos( substr( $html, $next, 220 ), 'e-parent' ) ) {
			$end = babayaga_child_div_end( $html, $next );

			if ( false !== $end && $end > $at ) {
				$start = $next;
			}
		}

		$seek = $next + 1;
	}

	if ( false === $start ) {
		return $html;
	}

	$end = babayaga_child_div_end( $html, $start );

	if ( false === $end ) {
		return $html;
	}

	return substr_replace( $html, babayaga_child_explore_strip(), $end, 0 );
}

/**
 * Close of an element that starts at $start, balanced on div tags.
 *
 * @param string $html Markup.
 * @param int    $start Offset of the opening <div.
 * @return int|false Offset just past the matching </div>.
 */
function babayaga_child_div_end( $html, $start ) {
	$depth = 0;

	if ( ! preg_match_all( '#<div\b|</div>#', $html, $tags, PREG_OFFSET_CAPTURE, $start ) ) {
		return false;
	}

	foreach ( $tags[0] as $tag ) {
		$depth += ( '</div>' === $tag[0] ) ? -1 : 1;

		if ( 0 === $depth ) {
			return $tag[1] + strlen( $tag[0] );
		}
	}

	return false;
}

/**
 * Remove photo galleries, section and heading together.
 *
 * The homepage gallery holds the informal phone shots the client called
 * "social photos". Its heading sits beside it in the same section, so the
 * whole section goes - a "See what's happening" heading over nothing would
 * be worse than no section at all.
 *
 * @param string $html Complete page HTML.
 * @return string
 */
function babayaga_child_remove_galleries( $html ) {
	while ( false !== ( $at = strpos( $html, 'elementor-widget-gallery' ) ) ) {
		// Walk out to the outermost section that owns this gallery.
		$start = false;
		$seek  = 0;

		while ( true ) {
			$next = strpos( $html, '<div class="elementor-element', $seek );

			if ( false === $next || $next > $at ) {
				break;
			}

			$tag = substr( $html, $next, 220 );

			if ( false !== strpos( $tag, 'e-parent' ) ) {
				$end = babayaga_child_div_end( $html, $next );

				if ( false !== $end && $end > $at ) {
					$start = $next;
				}
			}

			$seek = $next + 1;
		}

		if ( false === $start ) {
			// No owning section found; drop just the widget so we cannot loop.
			$w = strrpos( substr( $html, 0, $at ), '<div class="elementor-element' );
			$e = false === $w ? false : babayaga_child_div_end( $html, $w );

			if ( false === $e ) {
				return $html;
			}

			$html = substr_replace( $html, '', $w, $e - $w );

			continue;
		}

		$end = babayaga_child_div_end( $html, $start );

		if ( false === $end ) {
			return $html;
		}

		$html = substr_replace( $html, '', $start, $end - $start );
	}

	return $html;
}

/**
 * Drop containers left holding nothing.
 *
 * Removing a photograph from a two-column row leaves its column behind, and
 * an empty column keeps its half of the row - which is why the copy ended up
 * stranded in one half with a hole beside it. Anything with no widget and no
 * text left in it is removed, repeatedly, so nested wrappers collapse too.
 *
 * @param string $html Complete page HTML.
 * @return string
 */
function babayaga_child_drop_empty_containers( $html ) {
	for ( $pass = 0; $pass < 4; $pass++ ) {
		$removed = 0;
		$seek    = 0;

		while ( false !== ( $at = strpos( $html, '<div class="elementor-element', $seek ) ) ) {
			$end = babayaga_child_div_end( $html, $at );

			if ( false === $end ) {
				break;
			}

			$inner = substr( $html, $at, $end - $at );

			// Keep anything that still carries a widget, media or real text.
			$has_widget = false !== strpos( $inner, 'elementor-widget' );
			$has_media  = preg_match( '#<(?:img|iframe|video|svg|form|canvas)\b#i', $inner );
			$text       = trim( html_entity_decode( wp_strip_all_tags( $inner ) ) );

			if ( $has_widget || $has_media || '' !== $text ) {
				$seek = $at + 1;

				continue;
			}

			$html = substr_replace( $html, '', $at, $end - $at );
			$removed++;
			$seek = $at;
		}

		if ( ! $removed ) {
			break;
		}
	}

	return $html;
}

/**
 * Apply both markup changes to the finished page.
 *
 * @param string $html Complete page HTML.
 * @return string
 */
function babayaga_child_filter_output( $html ) {
	if ( '' === trim( (string) $html ) || false === stripos( $html, '<body' ) ) {
		return $html;
	}

	$uploads = wp_get_upload_dir();
	$base    = trailingslashit( $uploads['baseurl'] );

	$html = preg_replace_callback(
		'/<img\b[^>]*>/i',
		static function ( $m ) use ( $base ) {
			return babayaga_child_rewrite_img( $m[0], $base );
		},
		$html
	);

	$html = babayaga_child_rewrite_order_links( $html );
	$html = babayaga_child_fix_external_links( $html );
	$html = babayaga_child_update_hours( $html );
	$html = babayaga_child_fix_planning_cta( $html );
	$html = babayaga_child_inject_catering_form( $html );
	$html = babayaga_child_swap_menu( $html );
	$html = babayaga_child_dedupe_photos( $html, $base );
	$html = babayaga_child_add_srcset( $html, $base );

	$html = babayaga_child_inject_menu_pdf( $html );
	$html = babayaga_child_remove_dish_photos( $html );
	$html = babayaga_child_inject_explore_strip( $html );
	$html = babayaga_child_inject_happening( $html );

	if ( false === strpos( $html, 't101-hero-title' ) ) {
		$html = preg_replace(
			'/(<div\b[^>]*\bvintageBanner\b[^>]*>)/i',
			'${1}' . babayaga_child_hero_markup(),
			$html,
			1
		);
	}

	// Last, so the injected hero h1 is counted and kept as the page's one h1.
	return babayaga_child_fix_headings( $html );
}

/**
 * Buffer front-end output only. Admin, AJAX, REST, feeds and the Elementor
 * editor render their own documents and are left alone.
 */
function babayaga_child_start_buffer() {
	if ( is_admin() || wp_doing_ajax() || is_feed() || is_embed() ) {
		return;
	}

	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return;
	}

	if ( isset( $_GET['elementor-preview'] ) || isset( $_GET['action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}

	ob_start( 'babayaga_child_filter_output' );
}
add_action( 'template_redirect', 'babayaga_child_start_buffer', 1 );

/**
 * Persistent order bar.
 *
 * Ordering is the conversion action for this site but lived only in the
 * header, which scrolls away on phones. This pins it to the bottom of the
 * viewport on small screens once the hero has passed.
 */
function babayaga_child_order_bar() {
	if ( is_admin() ) {
		return;
	}
	?>
	<div class="t101-orderbar">
		<div class="t101-orderbar__text">
			<strong>101 Grill</strong>
			<span>Agoura Hills &middot; Open daily</span>
		</div>
		<?php $cta = babayaga_child_order_cta(); ?>
		<a class="t101-cta t101-cta--solid" href="<?php echo esc_url( $cta['url'] ); ?>"<?php echo ! empty( $cta['external'] ) ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( wp_strip_all_tags( html_entity_decode( $cta['label'] ) ) ); ?></a>
	</div>
	<?php
}
add_action( 'wp_footer', 'babayaga_child_order_bar' );

/**
 * inKind partner script.
 *
 * inKind (inkind.com) funds restaurants by buying food-and-beverage credit up
 * front and reselling it to diners, who get roughly 20% back. Partner venues
 * embed this loader so inKind can render its gift-card / payment modal.
 *
 * The file itself is a 267-byte bootstrapper: it sets a guard flag and injects
 * inkindscript.com/modal.js. It is loaded async and only on the front end, so
 * it never blocks rendering and never touches wp-admin or the Elementor editor.
 */
function babayaga_child_inkind_script() {
	if ( is_admin() ) {
		return;
	}

	if ( isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}

	echo '<script src="https://inkindscript.com/inkind.js" async></script>' . "\n";
}
add_action( 'wp_footer', 'babayaga_child_inkind_script', 20 );
