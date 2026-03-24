<?php
defined( 'ABSPATH' ) || exit;

/**
 * Generates a JPEG thumbnail from the first page of a PDF attachment.
 *
 * Priority order:
 *  1. Imagick (cleanest output)
 *  2. Ghostscript via exec() (fallback if Imagick unavailable)
 *  3. GD placeholder (graceful fallback with no server binaries)
 */
class R3DH_Thumbnail {

    /** Thumbnail dimensions (pixels) */
    const WIDTH  = 800;
    const HEIGHT = 1120;

    /**
     * @param int    $attachment_id  WP attachment ID of the PDF
     * @param string $title          Used as alt text / filename hint
     * @return int|WP_Error          Attachment ID of the generated thumbnail
     */
    public function generate( int $attachment_id, string $title ) {
        $pdf_path = get_attached_file( $attachment_id );

        if ( ! $pdf_path || ! file_exists( $pdf_path ) ) {
            return new WP_Error( 'file_not_found', __( 'PDF file not found on disk.', 'r3d-helper' ) );
        }

        $upload_dir  = wp_upload_dir();
        $thumb_name  = 'r3dh-thumb-' . $attachment_id . '-' . time() . '.jpg';
        $thumb_path  = trailingslashit( $upload_dir['path'] ) . $thumb_name;
        $thumb_url   = trailingslashit( $upload_dir['url'] )  . $thumb_name;

        $generated = $this->try_imagick( $pdf_path, $thumb_path )
                  || $this->try_ghostscript( $pdf_path, $thumb_path )
                  || $this->try_gd_placeholder( $thumb_path, $title );

        if ( ! $generated || ! file_exists( $thumb_path ) ) {
            return new WP_Error( 'thumb_failed', __( 'Could not generate thumbnail from PDF.', 'r3d-helper' ) );
        }

        return $this->register_as_attachment( $thumb_path, $thumb_url, $title );
    }

    /* ------------------------------------------------------------------ */
    /* Method 1 – Imagick                                                    */
    /* ------------------------------------------------------------------ */

    private function try_imagick( string $pdf_path, string $thumb_path ): bool {
        if ( ! extension_loaded( 'imagick' ) ) {
            return false;
        }

        try {
            $img = new Imagick();
            $img->setResolution( 150, 150 );
            $img->readImage( $pdf_path . '[0]' ); // first page only
            $img->setImageFormat( 'jpeg' );
            $img->setImageCompressionQuality( 85 );
            $img->thumbnailImage( self::WIDTH, self::HEIGHT, true );
            $img->flattenImages();
            $img->writeImage( $thumb_path );
            $img->clear();
            $img->destroy();
            return true;
        } catch ( Exception $e ) {
            // Log but continue to next method
            error_log( '[R3DH] Imagick error: ' . $e->getMessage() );
            return false;
        }
    }

    /* ------------------------------------------------------------------ */
    /* Method 2 – Ghostscript                                                */
    /* ------------------------------------------------------------------ */

    private function try_ghostscript( string $pdf_path, string $thumb_path ): bool {
        if ( ! function_exists( 'exec' ) ) {
            return false;
        }

        $gs = $this->find_ghostscript();
        if ( ! $gs ) {
            return false;
        }

        $cmd = sprintf(
            '%s -dNOPAUSE -dBATCH -dSAFER -dFirstPage=1 -dLastPage=1 '
            . '-sDEVICE=jpeg -dJPEGQ=85 -r150 '
            . '-dDEVICEWIDTHPOINTS=%d -dDEVICEHEIGHTPOINTS=%d '
            . '-sOutputFile=%s %s 2>&1',
            escapeshellcmd( $gs ),
            self::WIDTH,
            self::HEIGHT,
            escapeshellarg( $thumb_path ),
            escapeshellarg( $pdf_path )
        );

        exec( $cmd, $output, $return_code );

        if ( $return_code !== 0 ) {
            error_log( '[R3DH] Ghostscript error: ' . implode( "\n", $output ) );
            return false;
        }

        return file_exists( $thumb_path );
    }

    /** Try common gs binary names / paths */
    private function find_ghostscript() {
        $candidates = [ 'gs', 'gswin64c', 'gswin32c', '/usr/bin/gs', '/usr/local/bin/gs' ];
        foreach ( $candidates as $bin ) {
            exec( 'command -v ' . escapeshellarg( $bin ) . ' 2>/dev/null', $out, $code );
            if ( $code === 0 && ! empty( $out[0] ) ) {
                return trim( $out[0] );
            }
        }
        return false;
    }

    /* ------------------------------------------------------------------ */
    /* Method 3 – GD placeholder                                             */
    /* ------------------------------------------------------------------ */

    private function try_gd_placeholder( string $thumb_path, string $title ): bool {
        if ( ! extension_loaded( 'gd' ) ) {
            return false;
        }

        $w   = self::WIDTH;
        $h   = self::HEIGHT;
        $img = imagecreatetruecolor( $w, $h );

        $bg   = imagecolorallocate( $img, 240, 240, 245 );
        $fg   = imagecolorallocate( $img, 80, 100, 160 );
        $grey = imagecolorallocate( $img, 160, 160, 170 );

        imagefill( $img, 0, 0, $bg );

        // Simple book icon outline
        $cx = (int) ( $w / 2 );
        $cy = (int) ( $h / 2 ) - 60;
        imagerectangle( $img, $cx - 80, $cy - 100, $cx + 80, $cy + 100, $fg );
        imageline( $img, $cx, $cy - 100, $cx, $cy + 100, $fg );

        // Title text (built-in font)
        $short = mb_substr( $title, 0, 30 );
        imagestring( $img, 3, $cx - (int) ( strlen( $short ) * 3.5 ), $cy + 120, $short, $fg );
        imagestring( $img, 2, $cx - 60, $cy + 150, __( 'Flipbook Preview', 'r3d-helper' ), $grey );

        $ok = imagejpeg( $img, $thumb_path, 85 );
        imagedestroy( $img );

        return $ok;
    }

    /* ------------------------------------------------------------------ */
    /* Register the JPEG as a WP media attachment                            */
    /* ------------------------------------------------------------------ */

    private function register_as_attachment( string $thumb_path, string $thumb_url, string $title ) {
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $attachment = [
            'post_mime_type' => 'image/jpeg',
            'post_title'     => $title . ' — Flipbook Thumbnail',
            'post_content'   => '',
            'post_status'    => 'inherit',
        ];

        $attach_id = wp_insert_attachment( $attachment, $thumb_path );
        if ( is_wp_error( $attach_id ) ) {
            return $attach_id;
        }

        $meta = wp_generate_attachment_metadata( $attach_id, $thumb_path );
        wp_update_attachment_metadata( $attach_id, $meta );

        return $attach_id;
    }
}
