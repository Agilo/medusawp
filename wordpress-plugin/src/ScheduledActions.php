<?php

namespace MedusaWP;

use MedusaWP\Models\Product;

class ScheduledActions {
	/**
	 * Schedules import product thumbnail batch.
	 *
	 * @param integer $previous_post_id
	 * @param integer $sync_timestamp
	 * @return void
	 */
	public static function schedule_import_product_thumbnail_batch( int $previous_post_id, int $sync_timestamp ) {
		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;

		$product_table = MEDUSAWP_TABLE_PRODUCTS;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$products = $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT id, post_id FROM $product_table WHERE thumbnail IS NOT NULL AND post_id > %d ORDER BY post_id ASC LIMIT 100",
				array( $previous_post_id ?? 0 )
			),
			ARRAY_A
		);

		if ( ! $products || ! is_array( $products ) || empty( $products ) ) {
			return;
		}

		$post_id = null;
		foreach ( $products as $product ) {
			$product_id = $product['id'];
			$post_id    = $product['post_id'];

			as_enqueue_async_action( 'medusawp_import_product_thumbnail', array( $product_id, $sync_timestamp ) );
		}

		if ( $post_id ) {
			as_enqueue_async_action( 'medusawp_schedule_import_product_thumbnail_batch', array( $post_id, $sync_timestamp ) );
		}
	}

	/**
	 * Imports product thumbnail to media library and sets it as product post thumbnail.
	 *
	 * @param string       $product_id
	 * @param integer|null $sync_timestamp
	 * @return void
	 * @throws \Exception Throws exception if failed to import product thumbnail.
	 */
	public static function import_product_thumbnail( string $product_id, ?int $sync_timestamp = null ) {
		$product = medusawp_get_product( $product_id );

		$processing_item_id = Utils::save_sync_progress( 'thumbnail', $product ?? array( 'id' => $product_id ), $sync_timestamp );

		$processing_item_id = Models\SyncProgress::save(
			array(
				'model'             => 'thumbnail',
				'message'           => 'Syncing thumbnail of ' . $product_id . ' product...',
				'status'            => 'syncing',
				'data'              => $product ? wp_json_encode( $product ) : array( 'id' => $product_id ),
				'sync_timestamp'    => $sync_timestamp,
				'medusa_admin_link' => null,
				'started_at'        => time(),
			)
		);

		if ( ! $product ) {
			Models\SyncProgress::update(
				$processing_item_id,
				array(
					'status'   => 'error',
					'message'  => 'Thumbnail for product ' . $product_id . ' failed to import.',
					'data'     => wp_json_encode(
						array(
							'error' => __( 'Product not found.', 'medusawp' ),
							'id'    => $product_id,
						)
					),
					'ended_at' => time(),
				)
			);

			Utils::update_is_sync_finished( $sync_timestamp );

			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw new \Exception( "Product $product_id thumbnail import failed because product was not found in the DB." );
		}

		if ( ! $product['thumbnail'] ) {
			Models\SyncProgress::update(
				$processing_item_id,
				array(
					'status'            => 'error',
					'message'           => 'Thumbnail for product ' . $product_id . ' failed to import.',
					'medusa_admin_link' => null,
					'data'              => wp_json_encode(
						array(
							'error'   => __( 'Product don\'t have thumbnail.', 'medusawp' ),
							'product' => $product,
						)
					),
					'ended_at'          => time(),
				)
			);

			Utils::update_is_sync_finished( $sync_timestamp );

			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw new \Exception( "Product $product_id thumbnail import failed because product don't have thumbnail." );
		}

		// Bail out early if thumbnail already imported to media library and set as post thumbnail.
		if ( get_post_thumbnail_id( $product['post_id'] ) && ( $product['thumbnail'] === $product['thumbnail_imported'] ) ) {
			Models\SyncProgress::update(
				$processing_item_id,
				array(
					'status'            => 'success',
					'message'           => 'Thumbnail for product ' . $product_id . ' already imported.',
					'medusa_admin_link' => null,
					'data'              => wp_json_encode( $product ),
					'ended_at'          => time(),
				)
			);

			Utils::update_is_sync_finished( $sync_timestamp );

			return;
		}

		// Upload image to Media Library.
		$attachment_id = Utils::upload_image_from_url( $product['thumbnail'] );

		if ( ! $attachment_id || is_wp_error( $attachment_id ) ) {
			Models\SyncProgress::update(
				$processing_item_id,
				array(
					'status'            => 'error',
					'message'           => 'Thumbnail for product ' . $product_id . ' failed to import.',
					'medusa_admin_link' => null,
					'data'              => wp_json_encode(
						array(
							'errors'  => array( __( 'Uploading to Media Library failed.', 'medusawp' ), is_wp_error( $attachment_id ) ? $attachment_id->get_error_messages() : null ),
							'product' => $product,
						)
					),
					'ended_at'          => time(),
				)
			);

			Utils::update_is_sync_finished( $sync_timestamp );

			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw new \Exception( "Product $product_id thumbnail import failed." );
		}

		// Set image as post thumbnail.
		$set_thumbnail = set_post_thumbnail( $product['post_id'], $attachment_id );
		if ( ! $set_thumbnail ) {
			Models\SyncProgress::update(
				$processing_item_id,
				array(
					'status'            => 'error',
					'message'           => 'Thumbnail for product ' . $product_id . ' failed to import.',
					'medusa_admin_link' => null,
					'data'              => wp_json_encode(
						array(
							'error'         => __( 'Setting post thumbnail failed.', 'medusawp' ),
							'attachment_id' => $attachment_id,
							'product'       => $product,
						)
					),
					'ended_at'          => time(),
				)
			);

			Utils::update_is_sync_finished( $sync_timestamp );

			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw new \Exception( 'Failed to set post ' . $product['post_id'] . ' thumbnail.' );
		}

		Product::update( $product_id, array( 'thumbnail_imported' => $product['thumbnail'] ) );

		Utils::update_sync_progress(
			$processing_item_id,
			array_merge( $product, array( 'attachment_id' => $attachment_id ) ),
			null
		);

		Models\SyncProgress::update(
			$processing_item_id,
			array(
				'status'            => 'success',
				'message'           => 'Thumbnail for product ' . $product_id . ' imported.',
				'medusa_admin_link' => null,
				'data'              => wp_json_encode(
					array(
						'attachment_id' => $attachment_id,
						'product'       => $product,
					)
				),
				'ended_at'          => time(),
			)
		);

		Utils::update_is_sync_finished( $sync_timestamp );
	}
}
