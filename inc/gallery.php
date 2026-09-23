<?php
/**
 * The gallery manifest.
 *
 * One entry per photograph in assets/img/gallery/. Each has a 1000x667 tile
 * (`<key>.webp`) and a 1920x1280 full frame (`<key>-full.webp`). Captions
 * describe what is in the frame — never what a product does. See the README.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;

/**
 * Every photograph in the gallery, in the order they should appear.
 *
 * @return array[] Each: key, alt, caption.
 */
function cofifi_gallery_items() {
	$items = array(
		array( 'g01', __( 'Cofifi Coffee, 150 g, beside an Ethiopian basket', 'cofifi' ), __( '100% Ethiopian', 'cofifi' ) ),
		array( 'g02', __( 'A Cofifi Coffee bag on hessian with coffee cherries', 'cofifi' ), __( 'Cherry to bag', 'cofifi' ) ),
		array( 'g03', __( 'A Cofifi Coffee bag beside a poured latte', 'cofifi' ), __( 'The morning pour', 'cofifi' ) ),
		array( 'g04', __( 'The Cofifi range beside a steaming cup', 'cofifi' ), __( 'The full range', 'cofifi' ) ),
		array( 'g05', __( 'Water poured into a cup of coffee', 'cofifi' ), __( 'Just off the boil', 'cofifi' ) ),
		array( 'g06', __( 'Freshly ground coffee in a white cup', 'cofifi' ), __( 'Ground to order', 'cofifi' ) ),
		array( 'g07', __( 'The resealable pull tab on a Cofifi bag', 'cofifi' ), __( 'Pull tab to open', 'cofifi' ) ),
		array( 'g08', __( 'A kraft Cofifi bag in front of a roaster', 'cofifi' ), __( 'At the roastery', 'cofifi' ) ),
		array( 'g09', __( 'Three Cofifi bags on a slate bench with a scoop', 'cofifi' ), __( 'Three roasts, one bench', 'cofifi' ) ),
		array( 'g10', __( 'The Cofifi range laid out on a rattan mat', 'cofifi' ), __( 'Laid out on rattan', 'cofifi' ) ),
		array( 'g11', __( 'Cofifi bags against a terracotta wall with oranges', 'cofifi' ), __( 'Citrus and terracotta', 'cofifi' ) ),
		array( 'g12', __( 'Two Cofifi bags in a kitchen with copper pans', 'cofifi' ), __( 'A working kitchen', 'cofifi' ) ),
		array( 'g13', __( 'Two Cofifi bags beside a stovetop kettle', 'cofifi' ), __( 'Kettle on', 'cofifi' ) ),
		array( 'g14', __( 'Cofifi CBD + Coffee beside an espresso machine', 'cofifi' ), __( 'CBD + Coffee', 'cofifi' ) ),
		array( 'g15', __( 'Cofifi CBD + Coffee, 150 g', 'cofifi' ), __( 'CBD + Coffee, 150 g', 'cofifi' ) ),
		array( 'g16', __( 'A Cofifi bag on a table set with fruit and a cup', 'cofifi' ), __( 'Table set', 'cofifi' ) ),
		array( 'g17', __( 'The pull tab detail on a marble counter', 'cofifi' ), __( 'Resealable, every time', 'cofifi' ) ),
		array( 'g18', __( 'Rasta Roast beside a black stovetop kettle', 'cofifi' ), __( 'Rasta Roast · Mada Kush', 'cofifi' ) ),
		array( 'g19', __( 'Rasta Roast with a copper jug and green leaves', 'cofifi' ), __( 'Rasta Roast', 'cofifi' ) ),
		array( 'g20', __( 'Rasta Roast standing on hessian', 'cofifi' ), __( 'On hessian', 'cofifi' ) ),
		array( 'g21', __( 'Two Cofifi bags photographed outdoors', 'cofifi' ), __( 'Out of doors', 'cofifi' ) ),
		array( 'g22', __( 'The Cofifi range with a steaming cup and gold spoon', 'cofifi' ), __( 'Steam and a gold spoon', 'cofifi' ) ),
	);

	$out = array();
	foreach ( $items as $item ) {
		$out[] = array(
			'key'     => $item[0],
			'alt'     => $item[1],
			'caption' => $item[2],
			'tile'    => cofifi_img( 'gallery/' . $item[0] . '.webp' ),
			'full'    => cofifi_img( 'gallery/' . $item[0] . '-full.webp' ),
		);
	}

	/**
	 * Filter the gallery contents.
	 *
	 * @param array[] $out Gallery items.
	 */
	return apply_filters( 'cofifi_gallery_items', $out );
}

/**
 * A subset of the gallery, by key, in the order given.
 *
 * @param string[] $keys Item keys.
 * @return array[]
 */
function cofifi_gallery_pick( $keys ) {
	$by_key = array();
	foreach ( cofifi_gallery_items() as $item ) {
		$by_key[ $item['key'] ] = $item;
	}

	$out = array();
	foreach ( $keys as $key ) {
		if ( isset( $by_key[ $key ] ) ) {
			$out[] = $by_key[ $key ];
		}
	}
	return $out;
}

/**
 * One gallery tile. Shared by the homepage mosaic and the gallery page.
 *
 * @param array  $item  A gallery item.
 * @param int    $index Zero-based position, used by the lightbox.
 * @param string $class Extra classes for the figure.
 * @param bool   $eager Skip lazy loading — for tiles above the fold.
 */
function cofifi_gallery_tile( $item, $index, $class = '', $eager = false ) {
	?>
	<figure class="gal<?php echo $class ? ' ' . esc_attr( $class ) : ''; ?>">
		<button type="button" class="gal__btn"
		        data-lightbox="<?php echo (int) $index; ?>"
		        aria-label="<?php echo esc_attr( sprintf( /* translators: %s: photo caption. */ __( 'Enlarge: %s', 'cofifi' ), $item['caption'] ) ); ?>">
			<img class="gal__img"
			     src="<?php echo esc_url( $item['tile'] ); ?>"
			     data-full="<?php echo esc_url( $item['full'] ); ?>"
			     data-caption="<?php echo esc_attr( $item['caption'] ); ?>"
			     alt="<?php echo esc_attr( $item['alt'] ); ?>"
			     width="1000" height="667"
			     <?php echo $eager ? 'fetchpriority="high"' : 'loading="lazy" decoding="async"'; ?>>
			<span class="gal__veil" aria-hidden="true"></span>
			<span class="gal__zoom" aria-hidden="true"><?php cofifi_icon( 'search', 18 ); ?></span>
		</button>
		<figcaption class="gal__cap"><?php echo esc_html( $item['caption'] ); ?></figcaption>
	</figure>
	<?php
}

/**
 * The lightbox shell. Printed once per page that shows gallery tiles.
 */
function cofifi_lightbox() {
	?>
	<div class="lb" id="cofifi-lightbox" hidden>
		<div class="lb__scrim" data-lb-close></div>
		<figure class="lb__frame">
			<img class="lb__img" src="" alt="">
			<figcaption class="lb__cap"></figcaption>
		</figure>
		<button type="button" class="lb__btn lb__btn--close" data-lb-close aria-label="<?php esc_attr_e( 'Close', 'cofifi' ); ?>"><?php cofifi_icon( 'close', 20 ); ?></button>
		<button type="button" class="lb__btn lb__btn--prev" data-lb-step="-1" aria-label="<?php esc_attr_e( 'Previous photo', 'cofifi' ); ?>"><?php cofifi_icon( 'chevron', 20 ); ?></button>
		<button type="button" class="lb__btn lb__btn--next" data-lb-step="1" aria-label="<?php esc_attr_e( 'Next photo', 'cofifi' ); ?>"><?php cofifi_icon( 'chevron', 20 ); ?></button>
		<p class="lb__count small"><span data-lb-index>1</span> / <span data-lb-total>1</span></p>
	</div>
	<?php
}
