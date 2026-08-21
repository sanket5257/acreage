<?php
/**
 * One-click demo content for the Africa Game Farms theme.
 *
 * Creates the marketing pages, a primary/footer menu and a couple of sample
 * posts so a fresh install looks like the real site immediately.
 *
 * Two rules this obeys strictly:
 *
 *  1. Everything it creates is recorded (post IDs, menu IDs, and the reading
 *     settings it overwrote), so "Remove demo content" puts the site back.
 *  2. It never edits or deletes anything it did not create. Every post also
 *     carries the _acreage_demo meta flag as a second safety net.
 *
 * Listings are NOT owned here — the listing post type belongs to the Acreage
 * Core plugin. When that plugin is active the importer fills it with sample
 * farms through its post type; when it is not there is no post type to write
 * to, so the screen refuses the import rather than quietly producing a demo
 * with an empty Farms archive.
 */

defined( 'ABSPATH' ) || exit;

class Acreage_Demo_Import {

	/** @var string Option holding everything this importer created. */
	const OPTION = 'acreage_demo_content';

	/** @var string Meta flag stamped on every imported post. */
	const META = '_acreage_demo';

	/**
	 * Attachment IDs created for listing galleries during this import.
	 *
	 * Collected here rather than returned, because dress_listing() is called
	 * deep inside the loop; the caller folds them into the removal state so the
	 * remover deletes them along with everything else.
	 *
	 * @var int[]
	 */
	private $gallery_attachments = array();

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_post_acreage_demo_import', array( $this, 'handle_import' ) );
		add_action( 'admin_post_acreage_demo_remove', array( $this, 'handle_remove' ) );
	}

	/* ------------------------------------------------------------------ UI */

	public function add_page() {
		add_theme_page(
			__( 'AGF Demo Content', 'acreage' ),
			__( 'Demo Content', 'acreage' ),
			'manage_options',
			'acreage-demo',
			array( $this, 'render_page' )
		);
	}

	private function state() {
		$state = get_option( self::OPTION, array() );
		return is_array( $state ) ? $state : array();
	}

	/**
	 * Record what has been created so far.
	 *
	 * Called at checkpoints through the import, not only at the end, so an
	 * interrupted run still leaves a removable trail.
	 */
	private function save( $state ) {
		$state['posts'] = array_values( array_unique( array_map( 'intval', $state['posts'] ) ) );
		update_option( self::OPTION, $state );
	}

	private function is_imported() {
		$state = $this->state();
		return ! empty( $state['posts'] );
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that.', 'acreage' ) );
		}

		$state    = $this->state();
		$imported = $this->is_imported();
		$notice   = isset( $_GET['acreage-demo'] ) ? sanitize_key( $_GET['acreage-demo'] ) : '';

		/*
		 * Counts come from the definitions rather than being typed into the copy.
		 * This list promised "three sample farm listings" long after it had grown
		 * to twelve, which reads as a half-finished import to anyone who counts.
		 */
		$has_listings  = post_type_exists( 'listing' );
		$listing_count = count( $this->listing_definitions() );
		$article_count = count( $this->post_definitions() );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Demo Content', 'acreage' ); ?></h1>

			<?php if ( 'imported' === $notice ) : ?>
				<div class="notice notice-success is-dismissible"><p>
					<?php
					printf(
						/* translators: %d: number of items created. */
						esc_html__( 'Demo content imported — %d items created.', 'acreage' ),
						isset( $state['posts'] ) ? count( $state['posts'] ) : 0
					);
					?>
				</p></div>
			<?php elseif ( 'removed' === $notice ) : ?>
				<div class="notice notice-success is-dismissible"><p>
					<?php esc_html_e( 'Demo content removed and your reading settings restored.', 'acreage' ); ?>
				</p></div>
			<?php elseif ( 'noplugin' === $notice ) : ?>
				<div class="notice notice-error is-dismissible"><p>
					<?php esc_html_e( 'Import refused — the Acreage Core plugin is not active, so there is no Farms post type to import the sample farms into.', 'acreage' ); ?>
				</p></div>
			<?php elseif ( 'exists' === $notice ) : ?>
				<div class="notice notice-warning is-dismissible"><p>
					<?php esc_html_e( 'Demo content is already imported. Remove it first if you want a clean re-import.', 'acreage' ); ?>
				</p></div>
			<?php endif; ?>

			<?php if ( ! $has_listings ) : ?>
				<div class="notice notice-error"><p>
					<strong><?php esc_html_e( 'Activate the Acreage Core plugin first.', 'acreage' ); ?></strong>
					<?php esc_html_e( 'The plugin owns the Farms post type. Importing without it leaves the Farms archive empty and the homepage bands blank, which looks like a broken theme.', 'acreage' ); ?>
					<a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>"><?php esc_html_e( 'Go to Plugins', 'acreage' ); ?></a>
				</p></div>
			<?php endif; ?>

			<p style="max-width:60em">
				<?php esc_html_e( 'Builds the marketing pages, a primary and footer menu, the sample articles and the sample farms, then sets the homepage. Nothing you have written is touched — the remover only deletes what this tool created.', 'acreage' ); ?>
			</p>

			<h2><?php esc_html_e( 'What gets created', 'acreage' ); ?></h2>
			<ul class="ul-disc">
				<li><?php esc_html_e( 'Pages: Home, Farms for Sale, About Us, Contact Us, Articles & News', 'acreage' ); ?></li>
				<li><?php esc_html_e( 'Home set as the static front page, Articles & News as the posts page', 'acreage' ); ?></li>
				<li><?php esc_html_e( 'Menus: “AGF Primary” and “AGF Footer”, assigned to their theme locations', 'acreage' ); ?></li>
				<li>
					<?php
					printf(
						/* translators: %d: number of sample articles. */
						esc_html( _n( '%d sample article', '%d sample articles', $article_count, 'acreage' ) ),
						(int) $article_count
					);
					?>
				</li>
				<li>
					<?php if ( $has_listings ) : ?>
						<?php
						printf(
							/* translators: %d: number of sample farm listings. */
							esc_html( _n(
								'%d sample farm listing, with photographs, price, extent, province, region and species',
								'%d sample farm listings, with photographs, prices, extents, provinces, regions and species',
								$listing_count,
								'acreage'
							) ),
							(int) $listing_count
						);
						?>
					<?php else : ?>
						<em><?php esc_html_e( 'Farm listings cannot be created — the Acreage Core plugin is not active. Listings belong to the plugin, never the theme.', 'acreage' ); ?></em>
					<?php endif; ?>
				</li>
			</ul>

			<?php if ( $imported ) : ?>
				<h2><?php esc_html_e( 'Currently imported', 'acreage' ); ?></h2>
				<p>
					<?php
					printf(
						/* translators: 1: item count, 2: import date. */
						esc_html__( '%1$d items, imported %2$s.', 'acreage' ),
						count( $state['posts'] ),
						esc_html( isset( $state['when'] ) ? $state['when'] : __( 'unknown', 'acreage' ) )
					);
					?>
				</p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
					onsubmit="return confirm('<?php echo esc_js( __( 'Delete all demo content? Anything you edited inside it will be lost.', 'acreage' ) ); ?>');">
					<input type="hidden" name="action" value="acreage_demo_remove">
					<?php wp_nonce_field( 'acreage_demo_remove' ); ?>
					<?php submit_button( __( 'Remove demo content', 'acreage' ), 'delete', 'submit', false ); ?>
				</form>
			<?php else : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="acreage_demo_import">
					<?php wp_nonce_field( 'acreage_demo_import' ); ?>
					<?php
					submit_button(
						__( 'Import demo content', 'acreage' ),
						'primary',
						'submit',
						false,
						$has_listings ? array() : array( 'disabled' => 'disabled' )
					);
					?>
				</form>
				<p class="description">
					<?php esc_html_e( 'The import copies around sixty photographs into the media library and generates their thumbnails, so allow it up to a minute on a shared host.', 'acreage' ); ?>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}

	/* -------------------------------------------------------------- Import */

	public function handle_import() {
		check_admin_referer( 'acreage_demo_import' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that.', 'acreage' ) );
		}

		if ( $this->is_imported() ) {
			$this->redirect( 'exists' );
		}

		/*
		 * Refuse rather than half-import.
		 *
		 * Without the plugin the demo builds pages whose farm grids, category
		 * cards and featured band all query a post type that does not exist, so
		 * the customer's first look at the theme is a homepage of empty bands.
		 * Better to send them to activate the plugin and come back.
		 */
		if ( ! post_type_exists( 'listing' ) ) {
			$this->redirect( 'noplugin' );
		}

		/*
		 * Around sixty photographs are copied into the media library and each one
		 * has its thumbnails generated, which is comfortably past the 30 second
		 * default on most shared hosts. Ask for the headroom; hosts that forbid it
		 * simply ignore these, and the checkpointing below covers that case.
		 */
		if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) ) {
			@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		}

		wp_raise_memory_limit( 'image' );

		$state = array(
			'posts' => array(),
			'menus' => array(),
			'when'  => current_time( 'mysql' ),
			'prev'  => array(
				'show_on_front'  => get_option( 'show_on_front' ),
				'page_on_front'  => (int) get_option( 'page_on_front' ),
				'page_for_posts' => (int) get_option( 'page_for_posts' ),
				'nav_locations'  => get_theme_mod( 'nav_menu_locations', array() ),
			),
		);

		/*
		 * Listings are created further down, but the homepage and the archive
		 * layout query them as they render. Build the pages first and generate
		 * their CSS at the end, once there is inventory for the widgets to find.
		 */
		$pages = array();
		$slots = array();

		foreach ( $this->page_definitions() as $key => $page ) {
			$id = $this->insert_post( array(
				'post_type'    => 'page',
				'post_title'   => $page['title'],
				'post_content' => isset( $page['content'] ) ? $page['content'] : '',
				'post_status'  => 'publish',
				'menu_order'   => $page['order'],
			) );

			if ( ! $id ) {
				continue;
			}

			$pages[ $key ]    = $id;
			$state['posts'][] = $id;
		}

		$this->save( $state );

		// Hero image on the homepage.
		if ( ! empty( $pages['home'] ) ) {
			$hero = $this->sideload( 'hero.jpg', $pages['home'], __( 'Bushveld at first light', 'acreage' ) );
			if ( $hero ) {
				$state['posts'][] = $hero;
				set_post_thumbnail( $pages['home'], $hero );

				// The showcase template renders its own hero, so the content stays clean.
			}
		}

		foreach ( $this->post_definitions() as $post ) {
			$id = $this->insert_post( array(
				'post_type'    => 'post',
				'post_title'   => $post['title'],
				'post_content' => $post['content'],
				'post_excerpt' => $post['excerpt'],
				'post_status'  => 'publish',
			) );
			if ( $id ) {
				$state['posts'][] = $id;

				if ( ! empty( $post['image'] ) ) {
					$att = $this->sideload( $post['image'], $id, $post['title'] );
					if ( $att ) {
						$state['posts'][] = $att;
						set_post_thumbnail( $id, $att );
					}
				}
			}
		}

		$this->save( $state );

		// Sample listings only if the plugin that owns them is active.
		if ( post_type_exists( 'listing' ) ) {
			foreach ( $this->listing_definitions() as $listing ) {
				$id = $this->insert_post( array(
					'post_type'    => 'listing',
					'post_title'   => $listing['title'],
					'post_content' => $listing['content'],
					'post_status'  => 'publish',
				) );
				if ( $id ) {
					$state['posts'][] = $id;

					if ( ! empty( $listing['image'] ) ) {
						$att = $this->sideload( $listing['image'], $id, $listing['title'] );
						if ( $att ) {
							$state['posts'][] = $att;
							set_post_thumbnail( $id, $att );
						}
					}

					$this->dress_listing( $id, $listing );

					// Fill the Featured band, otherwise it imports empty and reads
					// as a broken section rather than an unused one.
					if ( ! empty( $listing['featured'] ) ) {
						update_post_meta( $id, 'acreage_featured', '1' );
					}

					/*
					 * Record after every farm. Each one costs half a dozen image
					 * resizes, so this is where a slow host runs out of time — and
					 * a timeout that leaves nothing recorded leaves the customer
					 * with content "Remove demo content" cannot see or clean up.
					 */
					$this->save( array_merge(
						$state,
						array( 'posts' => array_merge( $state['posts'], $this->gallery_attachments ) )
					) );
				}
			}

			/*
			 * Gallery attachments are created inside dress_listing(), so fold them
			 * into the removal state here. Miss this and "Remove demo content"
			 * leaves a media library full of orphaned farm photographs.
			 */
			if ( $this->gallery_attachments ) {
				$state['posts'] = array_merge( $state['posts'], $this->gallery_attachments );
			}
		}

		$this->save( $state );

		// Reading settings.
		if ( ! empty( $pages['home'] ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $pages['home'] );
		}
		if ( ! empty( $pages['articles'] ) ) {
			update_option( 'page_for_posts', $pages['articles'] );
		}

		// Menus.
		$locations = get_theme_mod( 'nav_menu_locations', array() );
		if ( ! is_array( $locations ) ) {
			$locations = array();
		}

		/*
		 * The comp's menu, not a list of every page.
		 *
		 *   Farms for sale ▾   (Game farms · Cattle farms · Browse by province)
		 *   Sell your farm
		 *   About
		 *   Contact
		 *
		 * No "Home" item — the wordmark is the home link, which is what the comp
		 * does and what saves the row from wrapping on a laptop. Articles is
		 * reachable from the footer rather than competing for space up top.
		 */
		$primary_id = $this->build_menu(
			__( 'AGF Primary', 'acreage' ),
			array( $pages['farms'], $pages['about'], $pages['contact'] ),
			$pages
		);

		if ( $primary_id ) {
			$state['menus'][]     = $primary_id;
			$locations['primary'] = $primary_id;

			$farms_item = $this->menu_item_for_page( $primary_id, $pages['farms'] );

			if ( $farms_item ) {
				$this->add_farm_submenu( $primary_id, $farms_item, $pages['farms'] );
			}

			// "Sell your farm" is an anchor on the homepage, not a page.
			wp_update_nav_menu_item( $primary_id, 0, array(
				'menu-item-title'    => __( 'Sell your farm', 'acreage' ),
				'menu-item-url'      => home_url( '/#sell' ),
				'menu-item-type'     => 'custom',
				'menu-item-status'   => 'publish',
				'menu-item-position' => 2,   // second, as in the comp
			) );
		}

		$footer_id = $this->build_menu(
			__( 'AGF Footer', 'acreage' ),
			array( $pages['farms'], $pages['about'], $pages['articles'], $pages['contact'], $pages['disclaimer'] ),
			$pages
		);
		if ( $footer_id ) {
			$state['menus'][]    = $footer_id;
			$locations['footer'] = $footer_id;
		}

		set_theme_mod( 'nav_menu_locations', $locations );

		// Seed the contact details the header and footer display.
		$content = get_option( 'acreage_home_content', array() );
		$content = is_array( $content ) ? $content : array();
		$content += array(
			'phone'   => '+27 82 441 7118',
			'fax'     => '086 618 0920',
			'email'   => 'info@africagamefarms.co.za',
			'tagline' => __( 'Game &amp; cattle farms for sale', 'acreage' ),
		);
		update_option( 'acreage_home_content', $content );

		/*
		 * The wordmark reads from the WordPress site title. A fresh install still
		 * carries whatever the installer typed — here "agf" — so the header showed
		 * an abbreviation where the comp shows the company name.
		 *
		 * Only replaced when the title is still a placeholder. Overwriting a name
		 * a client has already set would be worse than leaving it wrong.
		 */
		$title = get_option( 'blogname' );

		if ( ! $title || in_array( strtolower( $title ), array( 'agf', 'my site', 'just another wordpress site' ), true ) ) {
			$state['prev']['blogname']        = $title;
			$state['prev']['blogdescription'] = get_option( 'blogdescription' );

			update_option( 'blogname', 'Africa Game Farms' );
			update_option( 'blogdescription', __( 'Game &amp; cattle farms for sale', 'acreage' ) );
		}

		/*
		 * BUILD THE PAGES LAST — the ordering matters.
		 *
		 * The builders read the site as it stands: the header embeds the primary
		 * menu's ID, the About section bakes in a live farm count, the Featured
		 * band queries for featured farms. Building before the menus, listings and
		 * reading settings exist produced a header saying "create a menu", a stats
		 * block reading "0 live farms", and an empty Featured band — all of which
		 * look like bugs to the client and are really just an import running in the
		 * wrong order.
		 */
		$slots = array();

		foreach ( $this->page_definitions() as $key => $page ) {
			if ( empty( $pages[ $key ] ) ) {
				continue;
			}

			$id    = $pages[ $key ];
			$built = $this->build_with_elementor( $id, isset( $page['builder'] ) ? $page['builder'] : '' );

			/*
			 * The full-width template exists to give Elementor a clean canvas. A
			 * page that did NOT get built keeps the normal template, or it renders
			 * with no container at all and reads as a broken stylesheet.
			 */
			if ( $built && ! empty( $page['template'] ) ) {
				update_post_meta( $id, '_wp_page_template', $page['template'] );
			}

			if ( $built && ! empty( $page['slot'] ) ) {
				$slots[ $page['slot'] ] = $id;
			}
		}

		/*
		 * Hand the slot pages to the layout system so the Elementor header and
		 * footer actually replace the coded ones. Without this the pages exist but
		 * nothing uses them, which looks like the import half-worked.
		 */
		if ( $slots && class_exists( 'Acreage_Elementor_Layout' ) ) {
			$existing                = Acreage_Elementor_Layout::settings();
			$state['prev']['layout'] = $existing;
			update_option( Acreage_Elementor_Layout::OPTION, array_merge( $existing, $slots ) );
		}

		$this->save( $state );

		$this->redirect( 'imported' );
	}

	/** Is Elementor actually installed and running? */
	private function elementor_active() {
		return did_action( 'elementor/loaded' ) || class_exists( '\Elementor\Plugin' );
	}

	/**
	 * Copy one of the theme's bundled demo images into the media library.
	 *
	 * Files ship inside the theme, so this never reaches out to the network.
	 * The attachment is flagged like every other demo item, so the remover
	 * deletes it (and its resized files) along with the rest.
	 *
	 * @return int Attachment ID, or 0 on failure.
	 */
	private function sideload( $file, $parent_id, $title ) {
		$path = get_template_directory() . '/assets/demo/' . $file;

		if ( ! file_exists( $path ) ) {
			return 0;
		}

		$contents = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( false === $contents ) {
			return 0;
		}

		$upload = wp_upload_bits( $file, null, $contents );
		if ( ! empty( $upload['error'] ) || empty( $upload['file'] ) ) {
			return 0;
		}

		$attachment_id = wp_insert_attachment(
			array(
				'post_mime_type' => 'image/jpeg',
				'post_title'     => $title,
				'post_status'    => 'inherit',
			),
			$upload['file'],
			$parent_id,
			true
		);

		if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
			return 0;
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';
		wp_update_attachment_metadata(
			$attachment_id,
			wp_generate_attachment_metadata( $attachment_id, $upload['file'] )
		);

		update_post_meta( $attachment_id, self::META, 1 );

		return (int) $attachment_id;
	}

	/** Block markup for a full-width image. */
	private function image_block( $attachment_id ) {
		$url = wp_get_attachment_image_url( $attachment_id, 'full' );

		if ( ! $url ) {
			return '';
		}

		return sprintf(
			"<!-- wp:image {\"id\":%1\$d,\"sizeSlug\":\"full\",\"align\":\"wide\"} -->\n" .
			"<figure class=\"wp-block-image alignwide size-full\"><img src=\"%2\$s\" alt=\"\" class=\"wp-image-%1\$d\"/></figure>\n" .
			"<!-- /wp:image -->\n\n",
			$attachment_id,
			esc_url( $url )
		);
	}

	/**
	 * Insert a post and stamp it as ours.
	 *
	 * @return int|false
	 */
	private function insert_post( $args ) {
		$id = wp_insert_post( $args, true );

		if ( is_wp_error( $id ) || ! $id ) {
			return false;
		}

		update_post_meta( $id, self::META, 1 );

		return (int) $id;
	}

	/**
	 * Create a menu and fill it with page links.
	 *
	 * @return int|false Menu term ID.
	 */
	/**
	 * Build a menu from page IDs.
	 *
	 * @param string $name     Menu name.
	 * @param int[]  $page_ids Pages, in order.
	 * @param array  $pages    Full page map (unused here, kept for callers).
	 * @return int|false
	 */
	private function build_menu( $name, $page_ids, $pages ) {
		// wp_create_nav_menu returns a WP_Error if a menu with this name exists.
		$menu_id = wp_create_nav_menu( $name );

		if ( is_wp_error( $menu_id ) ) {
			return false;
		}

		$order = 1;
		foreach ( $page_ids as $page_id ) {
			if ( ! $page_id ) {
				continue;
			}
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-object-id' => $page_id,
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $order++,
			) );
		}

		return (int) $menu_id;
	}

	/**
	 * Add the comp's child items under "Farms for sale".
	 *
	 * The comp shows a single dropdown — Game farms, Cattle farms, Browse by
	 * province — rather than a flat row of every page. Those are taxonomy views
	 * and an anchor, not pages, so they are custom links.
	 *
	 * @param int $menu_id   Menu to add to.
	 * @param int $parent_id The "Farms for sale" menu item ID.
	 * @param int $farms_page Farms for Sale page ID, for the archive base.
	 */
	private function add_farm_submenu( $menu_id, $parent_id, $farms_page ) {
		$base = $farms_page ? get_permalink( $farms_page ) : home_url( '/' );

		$children = array(
			__( 'Game farms', 'acreage' )        => add_query_arg( 'listing_category', 'game-farms', $base ),
			__( 'Cattle farms', 'acreage' )      => add_query_arg( 'listing_category', 'cattle-farms', $base ),
			__( 'Browse by province', 'acreage' ) => $base . '#provinces',
		);

		$order = 1;

		foreach ( $children as $label => $url ) {
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'     => $label,
				'menu-item-url'       => $url,
				'menu-item-type'      => 'custom',
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $parent_id,
				'menu-item-position'  => $order++,
			) );
		}
	}

	/**
	 * The menu item ID for a given page inside a menu.
	 *
	 * @param int $menu_id Menu.
	 * @param int $page_id Page.
	 * @return int
	 */
	private function menu_item_for_page( $menu_id, $page_id ) {
		foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $item ) {
			if ( 'page' === $item->object && (int) $item->object_id === (int) $page_id ) {
				return (int) $item->ID;
			}
		}

		return 0;
	}

	/* -------------------------------------------------------------- Remove */

	public function handle_remove() {
		check_admin_referer( 'acreage_demo_remove' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that.', 'acreage' ) );
		}

		$state = $this->state();

		// Delete only posts we created AND that still carry our flag.
		if ( ! empty( $state['posts'] ) ) {
			foreach ( $state['posts'] as $id ) {
				if ( get_post( $id ) && get_post_meta( $id, self::META, true ) ) {
					wp_delete_post( $id, true );
				}
			}
		}

		if ( ! empty( $state['menus'] ) ) {
			foreach ( $state['menus'] as $menu_id ) {
				if ( is_nav_menu( $menu_id ) ) {
					wp_delete_nav_menu( $menu_id );
				}
			}
		}

		$this->restore_settings( $state );

		delete_option( self::OPTION );

		$this->redirect( 'removed' );
	}

	/**
	 * Put the reading settings and menus back — without breaking the site.
	 *
	 * THE BUG THIS REPLACES
	 *
	 * The previous version restored the captured "before" values unconditionally.
	 * On a fresh install that snapshot is "front page: latest posts, no menus
	 * assigned", so removing the demo left a site with no homepage and no
	 * navigation — and because the demo pages had just been deleted too, it read
	 * as a completely broken site rather than a clean one.
	 *
	 * Restoring is still right. Restoring on top of work someone has done since
	 * is not. So each setting is put back only if the current value is one WE
	 * set and the target still exists.
	 *
	 * @param array $state Saved import state.
	 */
	private function restore_settings( $state ) {
		$prev = isset( $state['prev'] ) ? $state['prev'] : array();
		$ours = isset( $state['posts'] ) ? array_map( 'intval', (array) $state['posts'] ) : array();

		if ( ! $prev ) {
			return;
		}

		$front = (int) get_option( 'page_on_front' );
		$blog  = (int) get_option( 'page_for_posts' );

		/*
		 * Only touch the front page if it is still one of ours. If the client has
		 * since pointed the site at a page they built themselves, leave it alone —
		 * removing demo content must not take their homepage with it.
		 */
		if ( $front && in_array( $front, $ours, true ) ) {
			$target = isset( $prev['page_on_front'] ) ? (int) $prev['page_on_front'] : 0;

			if ( $target && 'publish' === get_post_status( $target ) ) {
				update_option( 'page_on_front', $target );
				update_option( 'show_on_front', isset( $prev['show_on_front'] ) ? $prev['show_on_front'] : 'page' );
			} else {
				// Nothing valid to go back to: fall back to the blog rather than
				// leaving page_on_front pointing at a post that no longer exists.
				update_option( 'page_on_front', 0 );
				update_option( 'show_on_front', 'posts' );
			}
		}

		if ( $blog && in_array( $blog, $ours, true ) ) {
			$target = isset( $prev['page_for_posts'] ) ? (int) $prev['page_for_posts'] : 0;
			update_option( 'page_for_posts', ( $target && 'publish' === get_post_status( $target ) ) ? $target : 0 );
		}

		/*
		 * Menu locations: drop only the assignments pointing at menus we deleted.
		 * Anything the client wired up themselves stays wired up.
		 */
		$locations = get_theme_mod( 'nav_menu_locations', array() );
		$deleted   = isset( $state['menus'] ) ? array_map( 'intval', (array) $state['menus'] ) : array();

		if ( is_array( $locations ) && $deleted ) {
			foreach ( $locations as $location => $menu_id ) {
				if ( in_array( (int) $menu_id, $deleted, true ) ) {
					unset( $locations[ $location ] );
				}
			}

			$previous = isset( $prev['nav_locations'] ) && is_array( $prev['nav_locations'] ) ? $prev['nav_locations'] : array();

			foreach ( $previous as $location => $menu_id ) {
				if ( is_nav_menu( $menu_id ) ) {
					$locations[ $location ] = $menu_id;
				}
			}

			set_theme_mod( 'nav_menu_locations', $locations );
		}

		// Layout slots pointing at deleted pages would render nothing at all.
		if ( class_exists( 'Acreage_Elementor_Layout' ) ) {
			$layout  = Acreage_Elementor_Layout::settings();
			$changed = false;

			foreach ( $layout as $slot => $page_id ) {
				if ( in_array( (int) $page_id, $ours, true ) || ! get_post( $page_id ) ) {
					unset( $layout[ $slot ] );
					$changed = true;
				}
			}

			if ( $changed ) {
				update_option( Acreage_Elementor_Layout::OPTION, $layout );
			}
		}
	}

	private function redirect( $result ) {
		wp_safe_redirect( admin_url( 'themes.php?page=acreage-demo&acreage-demo=' . $result ) );
		exit;
	}

	/* ------------------------------------------------------------ Content */

	private function p( $text ) {
		return "<!-- wp:paragraph -->\n<p>" . $text . "</p>\n<!-- /wp:paragraph -->\n\n";
	}

	private function h( $text, $level = 2 ) {
		return "<!-- wp:heading {\"level\":$level} -->\n<h$level>" . $text . "</h$level>\n<!-- /wp:heading -->\n\n";
	}

	/**
	 * Every page the demo creates, and how it is built.
	 *
	 * 'builder' names a method on Acreage_Elementor_Template. When Elementor is
	 * active the page is assembled from real widgets, so the client can open any
	 * of these in the editor and change anything. Without Elementor the classic
	 * 'content' is used instead and the site still reads correctly — the demo is
	 * never allowed to depend on a plugin being installed.
	 *
	 * 'slot' assigns the page to a layout slot (header, footer, archive, single)
	 * rather than leaving it in the menu as a page a visitor could stumble into.
	 */
	private function page_definitions() {
		return array(
			'home'       => array(
				'title'    => __( 'Home', 'acreage' ),
				'order'    => 1,
				'template' => 'page-full-width.php',
				'builder'  => 'build',
				'menu'     => true,
				'content'  => $this->p( __( 'Install Elementor to build this homepage visually.', 'acreage' ) ),
			),
			'farms'      => array(
				'title'    => __( 'Farms for Sale', 'acreage' ),
				'order'    => 2,
				'template' => 'page-full-width.php',
				'builder'  => 'build_archive',
				'menu'     => true,
				'content'  => $this->p( __( 'The farm listings archive is served by the theme.', 'acreage' ) ),
			),
			'about'      => array(
				'title'    => __( 'About Us', 'acreage' ),
				'order'    => 3,
				'template' => 'page-full-width.php',
				'builder'  => 'build_about',
				'menu'     => true,
				'content'  =>
					$this->h( __( 'Trading since 2008', 'acreage' ) ) .
					$this->p( __( 'Africa Game Farms specialises in game and cattle farms across Southern Africa, with the founder active in property since 2004.', 'acreage' ) ),
			),
			'contact'    => array(
				'title'    => __( 'Contact Us', 'acreage' ),
				'order'    => 4,
				'template' => 'page-full-width.php',
				'builder'  => 'build_contact',
				'menu'     => true,
				'content'  =>
					$this->h( __( 'Get in touch', 'acreage' ) ) .
					$this->p( __( 'Peet Venter — +27 82 441 7118 — info@africagamefarms.co.za', 'acreage' ) ),
			),
			'articles'   => array(
				'title'    => __( 'Articles & News', 'acreage' ),
				'order'    => 5,
				'template' => '',
				'builder'  => 'build_articles',
				'menu'     => true,
				'content'  => $this->p( __( 'Posts are listed here automatically.', 'acreage' ) ),
			),
			'disclaimer' => array(
				'title'    => __( 'Agency Disclaimer', 'acreage' ),
				'order'    => 6,
				'template' => 'page-full-width.php',
				'builder'  => 'build_disclaimer',
				'menu'     => false,   // Footer only, as on the client's current site.
				'content'  => $this->p( __( 'Particulars are supplied by the seller and do not form part of any offer or contract.', 'acreage' ) ),
			),

			/* ------------------------------------------------- layout slot pages */

			'slot_header' => array(
				'title'    => __( 'Site Header', 'acreage' ),
				'order'    => 90,
				'template' => 'page-full-width.php',
				'builder'  => 'build_header',
				'slot'     => 'header',
				'menu'     => false,
			),
			'slot_footer' => array(
				'title'    => __( 'Site Footer', 'acreage' ),
				'order'    => 91,
				'template' => 'page-full-width.php',
				'builder'  => 'build_footer',
				'slot'     => 'footer',
				'menu'     => false,
			),
			'slot_archive' => array(
				'title'    => __( 'Farms Archive Layout', 'acreage' ),
				'order'    => 92,
				'template' => 'page-full-width.php',
				'builder'  => 'build_archive',
				'slot'     => 'archive',
				'menu'     => false,
			),
			'slot_single' => array(
				'title'    => __( 'Single Farm Layout', 'acreage' ),
				'order'    => 93,
				'template' => 'page-full-width.php',
				'builder'  => 'build_single',
				'slot'     => 'single',
				'menu'     => false,
			),
		);
	}

	/**
	 * Build a page's Elementor document from a template method.
	 *
	 * Returns false when Elementor is absent or the builder produced nothing, so
	 * the caller can leave the classic content in place instead.
	 *
	 * @param int    $post_id Page to build.
	 * @param string $method  Method on Acreage_Elementor_Template.
	 * @return bool
	 */
	private function build_with_elementor( $post_id, $method ) {
		if ( ! $method || ! $this->elementor_active() ) {
			return false;
		}

		require_once get_template_directory() . '/inc/class-acreage-elementor-template.php';

		$builder = new Acreage_Elementor_Template();

		if ( ! method_exists( $builder, $method ) ) {
			return false;
		}

		$data = $builder->{$method}();

		if ( ! is_array( $data ) || ! $data ) {
			return false;
		}

		update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $post_id, '_elementor_template_type', 'wp-page' );
		update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
		update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );

		// Generate the page's CSS now, so the first view is not unstyled.
		if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
			$css = new \Elementor\Core\Files\CSS\Post( $post_id );
			$css->update();
		}

		return true;
	}

	private function post_definitions() {
		return array(
			array(
				'title'   => __( 'What to check before buying a game farm', 'acreage' ),
				'image'   => 'article-01.jpg',
				'excerpt' => __( 'Water, fencing, carrying capacity and land claims — the four things that decide whether a farm is worth the asking price.', 'acreage' ),
				'content' =>
					$this->p( __( 'Sample article. Water security, game fencing standards, carrying capacity and any land claim history are the four checks that matter most.', 'acreage' ) ) .
					$this->h( __( 'Water security', 'acreage' ) ) .
					$this->p( __( 'Boreholes, natural springs and seasonal rivers all carry different risk. Ask for yield tests, not assurances.', 'acreage' ) ),
			),
			array(
				'title'   => __( 'Understanding hectares, camps and carrying capacity', 'acreage' ),
				'image'   => 'article-02.jpg',
				'excerpt' => __( 'Size alone tells you very little. Here is how to read a farm’s real capacity.', 'acreage' ),
				'content' =>
					$this->p( __( 'Sample article. A 500ha farm in a high-rainfall region can carry far more game than 2,000ha of arid veld.', 'acreage' ) ),
			),
		);
	}

	/**
	 * Sample farms, complete with the figures and terms the cards display.
	 *
	 * An earlier version created these with only a title and a photograph, which
	 * left every card reading "Price on application" with no extent or province —
	 * the design looked broken on a fresh install. They now carry everything.
	 */
	private function listing_definitions() {
		return array(
			array(
				'title'    => __( 'Mopani Ridge Reserve', 'acreage' ),
				'featured' => true,
				'image'    => 'farm-01.jpg',
				// Extra photographs for the detail gallery. One image makes the
				// single-farm page look like a placeholder; the drone photography
				// is the product on this site.
				'gallery'  => array( 'farm-02.jpg', 'farm-04.jpg', 'farm-06.jpg', 'hero-alt.jpg', 'category-game.jpg' ),
				'excerpt'  => __( 'Big Five capable, fully game fenced, with a lodge and two boreholes.', 'acreage' ),
				'content'  => $this->p( __( 'Mixed bushveld on the Waterberg plateau, fully game fenced and set up for both hunting and photographic use. The ridge runs east to west across the northern third of the property, giving three distinct grazing zones and year-round shelter.', 'acreage' ) ) .
					$this->p( __( 'Access is off a tarred district road with 4 km of maintained gravel to the main gate. The property has never been subdivided.', 'acreage' ) ),
				'improvements' => $this->p( __( 'Main lodge of six en-suite rooms with a separate boma and plunge pool. Manager’s house of three bedrooms, two staff cottages and a workshop with a covered implement bay.', 'acreage' ) ) .
					$this->p( __( 'Two equipped boreholes delivering roughly 9 000 litres an hour between them, feeding four reservoirs and eleven drinking points. Perimeter is 2.4 m game fence, electrified on the northern and eastern boundaries.', 'acreage' ) ),
				'wildlife' => $this->p( __( 'Mixed bushveld dominated by mopani, with marula and leadwood along the drainage lines. Grazing is sweetveld on the southern flats turning to sourveld on the ridge.', 'acreage' ) ) .
					$this->p( __( 'Game counted on the last aerial survey includes buffalo, sable, kudu, nyala, waterbuck, impala and warthog, with leopard resident on the ridge.', 'acreage' ) ),
				'land_claims' => $this->p( __( 'No land claim has been lodged against this property. A clearance letter from the Regional Land Claims Commissioner is available to serious buyers on request.', 'acreage' ) ),
				'species'  => array( 'Buffalo', 'Sable', 'Kudu', 'Nyala', 'Waterbuck', 'Impala', 'Warthog', 'Leopard' ),
				'price'    => 28500000,
				'hectares' => 1240,
				'category' => 'Game farms',
				'province' => 'Limpopo',
				'region'   => 'Waterberg',
				'status'   => 'New listing',
				'big_five' => true,
			),
			array(
				'title'    => __( 'Karoo Vlakte', 'acreage' ),
				'image'    => 'farm-03.jpg',
				'gallery'  => array( 'farm-06.jpg', 'farm-04.jpg', 'karoo-grass-detail.jpg', 'category-cattle.jpg' ),
				'excerpt'  => __( 'Open veld with strong borehole water and full handling facilities.', 'acreage' ),
				'content'  => $this->p( __( 'Open Karoo veld running small stock and cattle, with reliable borehole water and a full set of handling facilities. The property carries roughly 340 large stock units on a normal season and has been rested on a four-camp rotation for the past six years.', 'acreage' ) ) .
					$this->p( __( 'Two thirds of the farm is level to gently undulating; the remainder rises to a low koppie on the western boundary which holds the best winter grazing.', 'acreage' ) ),
				'improvements' => $this->p( __( 'Four-bedroom homestead with a wraparound stoep, a separate two-bedroom cottage and quarters for four staff. Steel shearing shed with six stands and a wool press.', 'acreage' ) ) .
					$this->p( __( 'Full handling facilities: crush, loading ramp, scale and holding pens. Three equipped boreholes, two windmills and 14 km of internal fencing, all jackal-proofed in the last four years.', 'acreage' ) ),
				// Cattle listings hide the wildlife section, per the brief.
				'wildlife' => '',
				'land_claims' => $this->p( __( 'A claim was lodged over a neighbouring portion in 2004 and was dismissed in 2011. This property is unaffected and the finding is on record.', 'acreage' ) ),
				'species'  => array(),
				'price'    => 12400000,
				'hectares' => 3600,
				'category' => 'Cattle farms',
				'province' => 'Northern Cape',
				'region'   => 'Beaufort West',
				'status'   => '',
				'big_five' => false,
			),
			array(
				'title'    => __( 'Palala River Frontage', 'acreage' ),
				'featured' => true,
				'image'    => 'farm-05.jpg',
				'gallery'  => array( 'farm-01.jpg', 'farm-02.jpg', 'farm-04.jpg', 'hero.jpg', 'article-02.jpg' ),
				'excerpt'  => __( 'Two kilometres of river frontage and established plains game.', 'acreage' ),
				'content'  => $this->p( __( 'Two kilometres of frontage onto the Palala, with established plains game and mature riverine bush. The river runs strongly for eight months of the year and holds pools through the dry season.', 'acreage' ) ) .
					$this->p( __( 'The farm has been in one family since 1974 and is sold as a going concern with the game population included.', 'acreage' ) ),
				'improvements' => $this->p( __( 'Thatched main house of four bedrooms overlooking the river, plus a two-bedroom guest cottage set back in the bush. Double garage, workshop and a cold room.', 'acreage' ) ) .
					$this->p( __( 'One equipped borehole and a licensed river abstraction point. Perimeter is 2.4 m game fence in good repair; internal fencing divides the property into three camps.', 'acreage' ) ),
				'wildlife' => $this->p( __( 'Riverine forest along the frontage giving way to mixed bushveld and open grassland on the higher ground. Good winter grazing on the flood terraces.', 'acreage' ) ) .
					$this->p( __( 'Established plains game: kudu, impala, waterbuck, bushbuck, zebra and warthog, with bushpig and civet on the river line.', 'acreage' ) ),
				'land_claims' => $this->p( __( 'No claim lodged. Title is clean and the property has never been subdivided.', 'acreage' ) ),
				'species'  => array( 'Kudu', 'Impala', 'Waterbuck', 'Bushbuck', 'Zebra', 'Warthog', 'Bushpig' ),
				'price'    => 41000000,
				'hectares' => 2180,
				'category' => 'Game farms',
				'province' => 'Limpopo',
				'region'   => 'Lephalale',
				'status'   => __( 'New listing', 'acreage' ),
				'big_five' => false,
			),
			array(
				'title'    => __( 'Thabazimbi Bushveld', 'acreage' ),
				'image'    => 'farm-02.jpg',
				'gallery'  => array( 'farm-04.jpg', 'category-game.jpg', 'hero-alt.jpg' ),
				'excerpt'  => __( 'Game fenced bushveld under the Kransberg, run as a hunting farm since 1998.', 'acreage' ),
				'content'  => $this->p( __( 'Mixed bushveld lying under the southern slopes of the Kransberg, game fenced on all four sides and run as a commercial hunting farm since 1998. The bush is thick on the eastern half and opens onto turf flats towards the western boundary.', 'acreage' ) ) .
					$this->p( __( 'Access is 18 km of district gravel off the Thabazimbi road, maintained by the municipality. Two neighbours have dropped internal fences, giving the game a larger effective range.', 'acreage' ) ),
				'improvements' => $this->p( __( 'Hunting lodge sleeping ten in five twin rooms, with a lapa, skinning shed and walk-in cold room. Separate three-bedroom owner’s house and quarters for three staff.', 'acreage' ) ) .
					$this->p( __( 'One equipped borehole at 4 500 litres an hour feeding two reservoirs and six drinking points. Perimeter is 2.4 m game fence, replaced in sections over the last five years.', 'acreage' ) ),
				'wildlife' => $this->p( __( 'Sour bushveld with red bushwillow and silver cluster-leaf on the slopes, turning to sweeter grazing on the turf flats. Carrying capacity is comfortable at current numbers.', 'acreage' ) ) .
					$this->p( __( 'Resident game includes kudu, impala, blue wildebeest, zebra, warthog and duiker, with brown hyena recorded on camera traps.', 'acreage' ) ),
				'land_claims' => $this->p( __( 'No land claim has been lodged. A clearance letter dated 2021 is on file and available on request.', 'acreage' ) ),
				'species'  => array( 'Kudu', 'Impala', 'Blue wildebeest', 'Zebra', 'Warthog', 'Duiker' ),
				'price'    => 16800000,
				'hectares' => 890,
				'category' => 'Game farms',
				'province' => 'Limpopo',
				'region'   => 'Thabazimbi',
				'status'   => __( 'New listing', 'acreage' ),
				'big_five' => false,
			),
			array(
				'title'    => __( 'Vaalkop Cattle Company', 'acreage' ),
				'image'    => 'category-cattle.jpg',
				'gallery'  => array( 'farm-06.jpg', 'farm-03.jpg', 'karoo-grass-detail.jpg' ),
				'excerpt'  => __( 'A working Bonsmara operation on mixed sweetveld, sold as a going concern.', 'acreage' ),
				'content'  => $this->p( __( 'A working Bonsmara operation on mixed sweetveld north of the Magaliesberg, running a breeding herd of 320 cows on a twelve-camp rotation. The farm has been under the same management for nineteen years and the herd records go back to 2006.', 'acreage' ) ) .
					$this->p( __( 'Roughly 200 hectares are arable and have been planted to maize and teff for on-farm feed. The balance is grazing in good condition.', 'acreage' ) ),
				'improvements' => $this->p( __( 'Six-bedroom homestead with a separate office wing, a three-bedroom manager’s house and quarters for eight staff. Two implement sheds, a workshop and a 400-tonne grain store.', 'acreage' ) ) .
					$this->p( __( 'Handling facilities include a covered crush, scale, loading ramp and hospital camp. Water comes from four equipped boreholes and a registered abstraction off the Vaalkop canal.', 'acreage' ) ),
				'wildlife' => '',
				'land_claims' => $this->p( __( 'A claim was lodged in 1999 over the eastern portion and was settled by financial compensation in 2014. The settlement agreement is registered against the title and does not affect transfer.', 'acreage' ) ),
				'species'  => array(),
				'price'    => 21500000,
				'hectares' => 1450,
				'category' => 'Cattle farms',
				'province' => 'North West',
				'region'   => 'Rustenburg',
				'status'   => '',
				'big_five' => false,
			),
			array(
				'title'    => __( 'Umfolozi Highlands', 'acreage' ),
				'featured' => true,
				'image'    => 'farm-06.jpg',
				'gallery'  => array( 'category-game.jpg', 'farm-01.jpg', 'hero.jpg', 'farm-05.jpg' ),
				'excerpt'  => __( 'Big Five capable, sharing an unfenced boundary with a provincial reserve.', 'acreage' ),
				'content'  => $this->p( __( 'Rolling thornveld sharing an eleven-kilometre unfenced boundary with a provincial reserve, which puts the full Big Five complement on the property for parts of the year. The land rises from 180 to 620 metres, giving unusually varied habitat for a single title.', 'acreage' ) ) .
					$this->p( __( 'The property is held in a single company and is offered with the shares, which is the cleanest route for a foreign buyer.', 'acreage' ) ),
				'improvements' => $this->p( __( 'Eight-suite photographic lodge on the escarpment edge, built in 2016 and trading with forward bookings. Staff village of eleven units, a workshop and a two-bay garage.', 'acreage' ) ) .
					$this->p( __( 'Three equipped boreholes and two perennial streams. Nineteen kilometres of the perimeter carry 2.4 m electrified game fence; the reserve boundary is deliberately open under a co-management agreement.', 'acreage' ) ),
				'wildlife' => $this->p( __( 'Acacia thornveld on the lower slopes giving way to mistbelt grassland on the plateau. The altitude range supports both lowveld and grassland species on one property.', 'acreage' ) ) .
					$this->p( __( 'Lion, elephant, buffalo, leopard and white rhino move through from the reserve. Resident game includes nyala, kudu, zebra, giraffe, impala and red duiker.', 'acreage' ) ),
				'land_claims' => $this->p( __( 'A community claim over the northern third was lodged in 2003 and remains under adjudication. Full documentation is available and the seller will discuss the position openly with serious buyers.', 'acreage' ) ),
				'species'  => array( 'Lion', 'Elephant', 'Buffalo', 'Leopard', 'White rhino', 'Nyala', 'Kudu', 'Zebra', 'Giraffe', 'Impala' ),
				'price'    => 47000000,
				'hectares' => 3100,
				'category' => 'Game farms',
				'province' => 'KwaZulu-Natal',
				'region'   => 'Hluhluwe',
				'status'   => __( 'New listing', 'acreage' ),
				'big_five' => true,
			),
			array(
				'title'    => __( 'Sneeuberg Merino Farm', 'acreage' ),
				'image'    => 'farm-03.jpg',
				'gallery'  => array( 'karoo-grass-detail.jpg', 'farm-04.jpg' ),
				'excerpt'  => __( 'Extensive Karoo grazing with a shearing shed and good winter camps.', 'acreage' ),
				'content'  => $this->p( __( 'Extensive Karoo grazing in the Sneeuberg foothills, running Merino sheep with a small cattle component. The high camps hold snow most winters, which the current owner uses to rest the low country through the growing season.', 'acreage' ) ) .
					$this->p( __( 'Rainfall averages 340 mm and falls mostly in summer. The farm has never been overstocked and the veld reflects it.', 'acreage' ) ),
				'improvements' => $this->p( __( 'Stone homestead of five bedrooms dating to 1908, restored in 2019, with a separate guest cottage and quarters for five staff. Eight-stand shearing shed with a wool press and sorting table.', 'acreage' ) ) .
					$this->p( __( 'Water is from two equipped boreholes, four windmills and eleven earth dams. Twenty-two camps, all jackal-proofed, with a crush and loading ramp at the main werf.', 'acreage' ) ),
				'wildlife' => '',
				'land_claims' => $this->p( __( 'No claim has been lodged against this property.', 'acreage' ) ),
				'species'  => array(),
				'price'    => 9600000,
				'hectares' => 4200,
				'category' => 'Cattle farms',
				'province' => 'Eastern Cape',
				'region'   => 'Graaff-Reinet',
				'status'   => '',
				'big_five' => false,
			),
			array(
				'title'    => __( 'Steenbokpan Game Ranch', 'acreage' ),
				'image'    => 'farm-04.jpg',
				'gallery'  => array( 'farm-02.jpg', 'hero-alt.jpg', 'category-game.jpg', 'farm-01.jpg' ),
				'excerpt'  => __( 'Breeding project with sable and roan, sold with the full game inventory.', 'acreage' ),
				'content'  => $this->p( __( 'A dedicated breeding operation on Kalahari sand west of Lephalale, set up in 2011 for sable and roan and run to a strict camp protocol since. The sand carries stock well in the wet years and the whole property drains freely.', 'acreage' ) ) .
					$this->p( __( 'Sold with the full game inventory, breeding records and the current veterinary permits.', 'acreage' ) ),
				'improvements' => $this->p( __( 'Manager’s house of four bedrooms, two staff cottages and a purpose-built breeding office with a walk-in safe for records. Boma complex of nine camps with individual water and feed points.', 'acreage' ) ) .
					$this->p( __( 'Three equipped boreholes delivering roughly 14 000 litres an hour in total. Perimeter is double 2.4 m game fence with a four-metre firebreak between the lines.', 'acreage' ) ),
				'wildlife' => $this->p( __( 'Open Kalahari bushveld with camelthorn and silver cluster-leaf, over sweet grazing that responds quickly after rain.', 'acreage' ) ) .
					$this->p( __( 'Breeding stock includes sable, roan, tsessebe and Livingstone eland, with a free-ranging population of kudu, impala and gemsbok.', 'acreage' ) ),
				'land_claims' => $this->p( __( 'No claim lodged. The property was consolidated from two titles in 2010 and both clearance letters are on file.', 'acreage' ) ),
				'species'  => array( 'Sable', 'Roan', 'Tsessebe', 'Eland', 'Kudu', 'Impala', 'Gemsbok' ),
				'price'    => 33000000,
				'hectares' => 2600,
				'category' => 'Game farms',
				'province' => 'Limpopo',
				'region'   => 'Lephalale',
				'status'   => '',
				'big_five' => false,
			),
			array(
				'title'    => __( 'Kalahari Duine', 'acreage' ),
				'image'    => 'hero-alt.jpg',
				'gallery'  => array( 'farm-06.jpg', 'category-game.jpg', 'farm-03.jpg' ),
				'excerpt'  => __( 'Red dune country with gemsbok and springbok, close to the Kgalagadi.', 'acreage' ),
				'content'  => $this->p( __( 'Red dune country running north towards the Kgalagadi, with the dune streets carrying the better grazing and the interdune flats holding water after rain. Six thousand eight hundred hectares in one title, unfenced internally.', 'acreage' ) ) .
					$this->p( __( 'The farm has been run as a low-intensity hunting and photographic property for two decades and is largely undeveloped by choice.', 'acreage' ) ),
				'improvements' => $this->p( __( 'Simple four-bedroom farmhouse with a wide stoep, a two-room guest unit and quarters for two staff. Solar plant with battery backup covers the whole werf; there is no Eskom connection.', 'acreage' ) ) .
					$this->p( __( 'Two equipped boreholes on solar pumps feeding five drinking points. Perimeter is stock fence, adequate for the species carried.', 'acreage' ) ),
				'wildlife' => $this->p( __( 'Duneveld with camelthorn along the interdune streets and grewia thicket on the slopes. Grazing is sweet and the property carries stock through dry years better than the rainfall suggests.', 'acreage' ) ) .
					$this->p( __( 'Gemsbok, springbok, red hartebeest, eland, steenbok and duiker, with brown hyena, black-backed jackal and Cape fox resident.', 'acreage' ) ),
				'land_claims' => $this->p( __( 'No claim lodged against this property.', 'acreage' ) ),
				'species'  => array( 'Gemsbok', 'Springbok', 'Red hartebeest', 'Eland', 'Steenbok', 'Duiker' ),
				'price'    => 24000000,
				'hectares' => 6800,
				'category' => 'Game farms',
				'province' => 'Northern Cape',
				'region'   => 'Askham',
				'status'   => '',
				'big_five' => false,
			),
			array(
				'title'    => __( 'Highveld Weiding', 'acreage' ),
				'image'    => 'farm-01.jpg',
				'gallery'  => array( 'category-cattle.jpg', 'karoo-grass-detail.jpg' ),
				'excerpt'  => __( 'Compact Free State grazing unit with good water, recently sold.', 'acreage' ),
				'content'  => $this->p( __( 'A compact grazing unit on the eastern Free State highveld, running weaners on sweet grassland with a small maize component for winter feed. Well suited to a first farm or to a neighbour looking to add carrying capacity.', 'acreage' ) ) .
					$this->p( __( 'Rainfall is reliable at around 680 mm. The property has good frontage onto a tarred road and is 14 km from Bethlehem.', 'acreage' ) ),
				'improvements' => $this->p( __( 'Three-bedroom homestead in fair order, a two-bay implement shed and quarters for three staff. Steel handling facility with a crush and loading ramp.', 'acreage' ) ) .
					$this->p( __( 'Water is from one equipped borehole and three earth dams, two of which hold through winter. Eight camps with internal fencing in good repair.', 'acreage' ) ),
				'wildlife' => '',
				'land_claims' => $this->p( __( 'No claim has been lodged.', 'acreage' ) ),
				'species'  => array(),
				'price'    => 8900000,
				'hectares' => 780,
				'category' => 'Cattle farms',
				'province' => 'Free State',
				'region'   => 'Bethlehem',
				'status'   => __( 'Sold', 'acreage' ),
				'big_five' => false,
			),
			array(
				'title'    => __( 'Crocodile River Estate', 'acreage' ),
				'featured' => true,
				'image'    => 'hero.jpg',
				'gallery'  => array( 'farm-05.jpg', 'article-02.jpg', 'farm-02.jpg', 'category-game.jpg' ),
				'excerpt'  => __( 'Small but exceptional, sharing a river boundary with the Kruger National Park.', 'acreage' ),
				'content'  => $this->p( __( 'Four hundred and sixty hectares sharing a two-kilometre river boundary with the Kruger National Park, which is what sets the price rather than the size. Big Five move across the river through the dry season and the sightings record kept since 2015 is available to buyers.', 'acreage' ) ) .
					$this->p( __( 'The property is zoned for tourism use and the existing lodge trades year-round at a high occupancy.', 'acreage' ) ),
				'improvements' => $this->p( __( 'Six-suite lodge on the river terrace with a raised viewing deck, plunge pool and boma, completed in 2018. Separate owner’s cottage set back from the guest area, plus staff accommodation for fourteen.', 'acreage' ) ) .
					$this->p( __( 'One equipped borehole, a licensed river abstraction and a 60 kVA generator on automatic changeover. Perimeter is 2.4 m electrified game fence on three sides; the river forms the fourth.', 'acreage' ) ),
				'wildlife' => $this->p( __( 'Riverine forest along the frontage with sycamore fig and jackalberry, opening to mixed lowveld bushveld inland. Small property, but the river makes it disproportionately productive.', 'acreage' ) ) .
					$this->p( __( 'Elephant, buffalo, lion and leopard cross regularly from the park. Resident game includes kudu, nyala, bushbuck, impala, waterbuck and hippo in the river pools.', 'acreage' ) ),
				'land_claims' => $this->p( __( 'No claim lodged. Title is clean and the tourism zoning is confirmed in writing by the municipality.', 'acreage' ) ),
				'species'  => array( 'Elephant', 'Buffalo', 'Lion', 'Leopard', 'Kudu', 'Nyala', 'Bushbuck', 'Impala', 'Waterbuck', 'Hippo' ),
				'price'    => 38500000,
				'hectares' => 460,
				'category' => 'Game farms',
				'province' => 'Mpumalanga',
				'region'   => 'Malelane',
				'status'   => __( 'New listing', 'acreage' ),
				'big_five' => true,
			),
			array(
				'title'    => __( 'Otjiwarongo Cattle Post', 'acreage' ),
				'image'    => 'farm-02.jpg',
				'gallery'  => array( 'farm-04.jpg', 'category-cattle.jpg', 'hero-alt.jpg' ),
				'excerpt'  => __( 'Large-scale Namibian weaner operation, currently off market.', 'acreage' ),
				'content'  => $this->p( __( 'A large-scale weaner operation on the Otjozondjupa thornveld, running roughly 900 head on an extensive rotation across thirty-one camps. Namibian weaners have an established market into South African feedlots and the farm has supplied the same buyer since 2009.', 'acreage' ) ) .
					$this->p( __( 'Offered as a going concern including the herd, implements and the existing supply contract.', 'acreage' ) ),
				'improvements' => $this->p( __( 'Main house of five bedrooms with a separate office, a three-bedroom manager’s house and a staff village of nine units. Workshop, two implement sheds and a 300-tonne feed store.', 'acreage' ) ) .
					$this->p( __( 'Eleven equipped boreholes on a piped reticulation network feeding thirty-four troughs. Handling facilities at three points across the farm, each with a crush and loading ramp.', 'acreage' ) ),
				'wildlife' => '',
				'land_claims' => $this->p( __( 'Namibian property. The South African restitution process does not apply; title is freehold and unencumbered.', 'acreage' ) ),
				'species'  => array(),
				'price'    => 19750000,
				'hectares' => 9400,
				'category' => 'Cattle farms',
				'province' => 'Namibia',
				'region'   => 'Otjozondjupa',
				'status'   => __( 'Off market', 'acreage' ),
				'big_five' => false,
			),
		);
	}

	/** Apply a sample farm's figures and terms, when the plugin provides them. */
	private function dress_listing( $post_id, $listing ) {
		if ( ! empty( $listing['excerpt'] ) ) {
			wp_update_post( array( 'ID' => $post_id, 'post_excerpt' => $listing['excerpt'] ) );
		}

		update_post_meta( $post_id, 'acreage_price', $listing['price'] );
		update_post_meta( $post_id, 'acreage_hectares', $listing['hectares'] );

		if ( ! empty( $listing['big_five'] ) ) {
			update_post_meta( $post_id, 'acreage_big_five', '1' );
		}

		/*
		 * The four labelled sections.
		 *
		 * These were never written, so every demo farm arrived with a one-line
		 * description and three empty headings. The detail page is where a buyer
		 * actually decides, and an empty Improvements block reads as an unfinished
		 * site rather than a farm without improvements.
		 */
		foreach ( array( 'improvements', 'wildlife', 'land_claims' ) as $field ) {
			if ( ! empty( $listing[ $field ] ) ) {
				update_post_meta( $post_id, 'acreage_' . $field, wp_kses_post( $listing[ $field ] ) );
			}
		}

		// Species chips on the detail page, and a filterable taxonomy besides.
		if ( ! empty( $listing['species'] ) && taxonomy_exists( 'species' ) ) {
			wp_set_object_terms( $post_id, (array) $listing['species'], 'species' );
		}

		/*
		 * The gallery.
		 *
		 * Stored as a comma-separated list of attachment IDs, which is what the
		 * field and the Farm Details widget both expect. Without it the detail
		 * page shows the single featured image and nothing else — the specific
		 * thing that made it look unfinished.
		 */
		if ( ! empty( $listing['gallery'] ) ) {
			$ids = array();

			foreach ( (array) $listing['gallery'] as $file ) {
				$att = $this->sideload( $file, $post_id, $listing['title'] );

				if ( $att ) {
					$ids[] = $att;
					$this->gallery_attachments[] = $att;
				}
			}

			if ( $ids ) {
				update_post_meta( $post_id, 'acreage_gallery', implode( ',', $ids ) );
			}
		}

		$terms = array(
			'listing_category' => $listing['category'],
			'province'         => $listing['province'],
			'region'           => $listing['region'],
			'status'           => $listing['status'],
		);

		foreach ( $terms as $taxonomy => $name ) {
			if ( ! $name || ! taxonomy_exists( $taxonomy ) ) {
				continue;
			}

			// Regions are not seeded by the plugin — they arrive with migration —
			// so create the one this sample needs rather than silently skipping it.
			$term = term_exists( $name, $taxonomy );
			if ( ! $term ) {
				$term = wp_insert_term( $name, $taxonomy );
			}

			if ( ! is_wp_error( $term ) ) {
				wp_set_object_terms( $post_id, (int) $term['term_id'], $taxonomy );
			}
		}

		// Let the plugin derive the size and price bands exactly as it would on save.
		if ( class_exists( 'Acreage_Core_Fields' ) && method_exists( 'Acreage_Core_Fields', 'assign_bands_for' ) ) {
			Acreage_Core_Fields::assign_bands_for( $post_id );
		}
	}
}
