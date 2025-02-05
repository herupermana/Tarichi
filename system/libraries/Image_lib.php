<?php

 if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/**
 * CodeIgniter.
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 *
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Image Manipulation class.
 *
 * @category	Image_lib
 *
 * @author		ExpressionEngine Dev Team
 *
 * @link		http://codeigniter.com/user_guide/libraries/image_lib.html
 */
class CI_Image_lib
{
    public $image_library = 'gd2';	// Can be:  imagemagick, netpbm, gd, gd2
    public $library_path = '';
    public $dynamic_output = false;	// Whether to send to browser or write to disk
    public $source_image = '';
    public $new_image = '';
    public $width = '';
    public $height = '';
    public $quality = '90';
    public $create_thumb = false;
    public $thumb_marker = '_thumb';
    public $maintain_ratio = true;		// Whether to maintain aspect ratio when resizing or use hard values
    public $master_dim = 'auto';	// auto, height, or width.  Determines what to use as the master dimension
    public $rotation_angle = '';
    public $x_axis = '';
    public $y_axis = '';

    // Watermark Vars
    public $wm_text = '';			// Watermark text if graphic is not used
    public $wm_type = 'text';		// Type of watermarking.  Options:  text/overlay
    public $wm_x_transp = 4;
    public $wm_y_transp = 4;
    public $wm_overlay_path = '';			// Watermark image path
    public $wm_font_path = '';			// TT font
    public $wm_font_size = 17;			// Font size (different versions of GD will either use points or pixels)
    public $wm_vrt_alignment = 'B';			// Vertical alignment:   T M B
    public $wm_hor_alignment = 'C';			// Horizontal alignment: L R C
    public $wm_padding = 0;			// Padding around text
    public $wm_hor_offset = 0;			// Lets you push text to the right
    public $wm_vrt_offset = 0;			// Lets you push  text down
    public $wm_font_color = '#ffffff';	// Text color
    public $wm_shadow_color = '';			// Dropshadow color
    public $wm_shadow_distance = 2;			// Dropshadow distance
    public $wm_opacity = 50;			// Image opacity: 1 - 100  Only works with image

    // Private Vars
    public $source_folder = '';
    public $dest_folder = '';
    public $mime_type = '';
    public $orig_width = '';
    public $orig_height = '';
    public $image_type = '';
    public $size_str = '';
    public $full_src_path = '';
    public $full_dst_path = '';
    public $create_fnc = 'imagecreatetruecolor';
    public $copy_fnc = 'imagecopyresampled';
    public $error_msg = [];
    public $wm_use_drop_shadow = false;
    public $wm_use_truetype = false;

    /**
     * Constructor.
     *
     * @param	string
     *
     * @return void
     */
    public function __construct($props = [])
    {
        if (count($props) > 0) {
            $this->initialize($props);
        }

        log_message('debug', 'Image Lib Class Initialized');
    }

    // --------------------------------------------------------------------

    /**
     * Initialize image properties.
     *
     * Resets values in case this class is used in a loop
     *
     * @return void
     */
    public function clear()
    {
        $props = ['source_folder', 'dest_folder', 'source_image', 'full_src_path', 'full_dst_path', 'new_image', 'image_type', 'size_str', 'quality', 'orig_width', 'orig_height', 'rotation_angle', 'x_axis', 'y_axis', 'create_fnc', 'copy_fnc', 'wm_overlay_path', 'wm_use_truetype', 'dynamic_output', 'wm_font_size', 'wm_text', 'wm_vrt_alignment', 'wm_hor_alignment', 'wm_padding', 'wm_hor_offset', 'wm_vrt_offset', 'wm_font_color', 'wm_use_drop_shadow', 'wm_shadow_color', 'wm_shadow_distance', 'wm_opacity'];

        foreach ($props as $val) {
            $this->$val = '';
        }

        // special consideration for master_dim
        $this->master_dim = 'auto';
    }

    // --------------------------------------------------------------------

    /**
     * initialize image preferences.
     *
     * @param	array
     *
     * @return bool
     */
    public function initialize($props = [])
    {
        /*
         * Convert array elements into class variables
         */
        if (count($props) > 0) {
            foreach ($props as $key => $val) {
                $this->$key = $val;
            }
        }

        /*
         * Is there a source image?
         *
         * If not, there's no reason to continue
         *
         */
        if ($this->source_image == '') {
            $this->set_error('imglib_source_image_required');

            return false;
        }

        /*
         * Is getimagesize() Available?
         *
         * We use it to determine the image properties (width/height).
         * Note:  We need to figure out how to determine image
         * properties using ImageMagick and NetPBM
         *
         */
        if (!function_exists('getimagesize')) {
            $this->set_error('imglib_gd_required_for_props');

            return false;
        }

        $this->image_library = strtolower($this->image_library);

        /*
         * Set the full server path
         *
         * The source image may or may not contain a path.
         * Either way, we'll try use realpath to generate the
         * full server path in order to more reliably read it.
         *
         */
        if (function_exists('realpath') and @realpath($this->source_image) !== false) {
            $full_source_path = str_replace('\\', '/', realpath($this->source_image));
        } else {
            $full_source_path = $this->source_image;
        }

        $x = explode('/', $full_source_path);
        $this->source_image = end($x);
        $this->source_folder = str_replace($this->source_image, '', $full_source_path);

        // Set the Image Properties
        if (!$this->get_image_properties($this->source_folder.$this->source_image)) {
            return false;
        }

        /*
         * Assign the "new" image name/path
         *
         * If the user has set a "new_image" name it means
         * we are making a copy of the source image. If not
         * it means we are altering the original.  We'll
         * set the destination filename and path accordingly.
         *
         */
        if ($this->new_image == '') {
            $this->dest_image = $this->source_image;
            $this->dest_folder = $this->source_folder;
        } else {
            if (strpos($this->new_image, '/') === false) {
                $this->dest_folder = $this->source_folder;
                $this->dest_image = $this->new_image;
            } else {
                if (function_exists('realpath') and @realpath($this->new_image) !== false) {
                    $full_dest_path = str_replace('\\', '/', realpath($this->new_image));
                } else {
                    $full_dest_path = $this->new_image;
                }

                // Is there a file name?
                if (!preg_match("#\.(jpg|jpeg|gif|png)$#i", $full_dest_path)) {
                    $this->dest_folder = $full_dest_path.'/';
                    $this->dest_image = $this->source_image;
                } else {
                    $x = explode('/', $full_dest_path);
                    $this->dest_image = end($x);
                    $this->dest_folder = str_replace($this->dest_image, '', $full_dest_path);
                }
            }
        }

        /*
         * Compile the finalized filenames/paths
         *
         * We'll create two master strings containing the
         * full server path to the source image and the
         * full server path to the destination image.
         * We'll also split the destination image name
         * so we can insert the thumbnail marker if needed.
         *
         */
        if ($this->create_thumb === false or $this->thumb_marker == '') {
            $this->thumb_marker = '';
        }

        $xp = $this->explode_name($this->dest_image);

        $filename = $xp['name'];
        $file_ext = $xp['ext'];

        $this->full_src_path = $this->source_folder.$this->source_image;
        $this->full_dst_path = $this->dest_folder.$filename.$this->thumb_marker.$file_ext;

        /*
         * Should we maintain image proportions?
         *
         * When creating thumbs or copies, the target width/height
         * might not be in correct proportion with the source
         * image's width/height.  We'll recalculate it here.
         *
         */
        if ($this->maintain_ratio === true && ($this->width != '' and $this->height != '')) {
            $this->image_reproportion();
        }

        /*
         * Was a width and height specified?
         *
         * If the destination width/height was
         * not submitted we will use the values
         * from the actual file
         *
         */
        if ($this->width == '') {
            $this->width = $this->orig_width;
        }

        if ($this->height == '') {
            $this->height = $this->orig_height;
        }

        // Set the quality
        $this->quality = trim(str_replace('%', '', $this->quality));

        if ($this->quality == '' or $this->quality == 0 or !is_numeric($this->quality)) {
            $this->quality = 90;
        }

        // Set the x/y coordinates
        $this->x_axis = ($this->x_axis == '' or !is_numeric($this->x_axis)) ? 0 : $this->x_axis;
        $this->y_axis = ($this->y_axis == '' or !is_numeric($this->y_axis)) ? 0 : $this->y_axis;

        // Watermark-related Stuff...
        if ($this->wm_font_color != '') {
            if (strlen($this->wm_font_color) == 6) {
                $this->wm_font_color = '#'.$this->wm_font_color;
            }
        }

        if ($this->wm_shadow_color != '') {
            if (strlen($this->wm_shadow_color) == 6) {
                $this->wm_shadow_color = '#'.$this->wm_shadow_color;
            }
        }

        if ($this->wm_overlay_path != '') {
            $this->wm_overlay_path = str_replace('\\', '/', realpath($this->wm_overlay_path));
        }

        if ($this->wm_shadow_color != '') {
            $this->wm_use_drop_shadow = true;
        }

        if ($this->wm_font_path != '') {
            $this->wm_use_truetype = true;
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Image Resize.
     *
     * This is a wrapper function that chooses the proper
     * resize function based on the protocol specified
     *
     * @return bool
     */
    public function resize()
    {
        $protocol = 'image_process_'.$this->image_library;

        if (preg_match('/gd2$/i', $protocol)) {
            $protocol = 'image_process_gd';
        }

        return $this->$protocol('resize');
    }

    // --------------------------------------------------------------------

    /**
     * Image Crop.
     *
     * This is a wrapper function that chooses the proper
     * cropping function based on the protocol specified
     *
     * @return bool
     */
    public function crop()
    {
        $protocol = 'image_process_'.$this->image_library;

        if (preg_match('/gd2$/i', $protocol)) {
            $protocol = 'image_process_gd';
        }

        return $this->$protocol('crop');
    }

    // --------------------------------------------------------------------

    /**
     * Image Rotate.
     *
     * This is a wrapper function that chooses the proper
     * rotation function based on the protocol specified
     *
     * @return bool
     */
    public function rotate()
    {
        // Allowed rotation values
        $degs = [90, 180, 270, 'vrt', 'hor'];

        if ($this->rotation_angle == '' or !in_array($this->rotation_angle, $degs)) {
            $this->set_error('imglib_rotation_angle_required');

            return false;
        }

        // Reassign the width and height
        if ($this->rotation_angle == 90 or $this->rotation_angle == 270) {
            $this->width = $this->orig_height;
            $this->height = $this->orig_width;
        } else {
            $this->width = $this->orig_width;
            $this->height = $this->orig_height;
        }

        // Choose resizing function
        if ($this->image_library == 'imagemagick' or $this->image_library == 'netpbm') {
            $protocol = 'image_process_'.$this->image_library;

            return $this->$protocol('rotate');
        }

        if ($this->rotation_angle == 'hor' or $this->rotation_angle == 'vrt') {
            return $this->image_mirror_gd();
        } else {
            return $this->image_rotate_gd();
        }
    }

    // --------------------------------------------------------------------

    /**
     * Image Process Using GD/GD2.
     *
     * This function will resize or crop
     *
     * @param	string
     *
     * @return bool
     */
    public function image_process_gd($action = 'resize')
    {
        $v2_override = false;

        // If the target width/height match the source, AND if the new file name is not equal to the old file name
        // we'll simply make a copy of the original with the new name... assuming dynamic rendering is off.
        if ($this->dynamic_output === false) {
            if ($this->orig_width == $this->width and $this->orig_height == $this->height) {
                if ($this->source_image != $this->new_image) {
                    if (@copy($this->full_src_path, $this->full_dst_path)) {
                        @chmod($this->full_dst_path, FILE_WRITE_MODE);
                    }
                }

                return true;
            }
        }

        // Let's set up our values based on the action
        if ($action == 'crop') {
            //  Reassign the source width/height if cropping
            $this->orig_width = $this->width;
            $this->orig_height = $this->height;

            // GD 2.0 has a cropping bug so we'll test for it
            if ($this->gd_version() !== false) {
                $gd_version = str_replace('0', '', $this->gd_version());
                $v2_override = ($gd_version == 2) ? true : false;
            }
        } else {
            // If resizing the x/y axis must be zero
            $this->x_axis = 0;
            $this->y_axis = 0;
        }

        //  Create the image handle
        if (!($src_img = $this->image_create_gd())) {
            return false;
        }

        //  Create The Image
        //
        //  old conditional which users report cause problems with shared GD libs who report themselves as "2.0 or greater"
        //  it appears that this is no longer the issue that it was in 2004, so we've removed it, retaining it in the comment
        //  below should that ever prove inaccurate.
        //
        //  if ($this->image_library == 'gd2' AND function_exists('imagecreatetruecolor') AND $v2_override == FALSE)
        if ($this->image_library == 'gd2' and function_exists('imagecreatetruecolor')) {
            $create = 'imagecreatetruecolor';
            $copy = 'imagecopyresampled';
        } else {
            $create = 'imagecreate';
            $copy = 'imagecopyresized';
        }

        $dst_img = $create($this->width, $this->height);

        if ($this->image_type == 3) { // png we can actually preserve transparency
            imagealphablending($dst_img, false);
            imagesavealpha($dst_img, true);
        }

        $copy($dst_img, $src_img, 0, 0, $this->x_axis, $this->y_axis, $this->width, $this->height, $this->orig_width, $this->orig_height);

        //  Show the image
        if ($this->dynamic_output == true) {
            $this->image_display_gd($dst_img);
        } else {
            // Or save it
            if (!$this->image_save_gd($dst_img)) {
                return false;
            }
        }

        //  Kill the file handles
        imagedestroy($dst_img);
        imagedestroy($src_img);

        // Set the file to 777
        @chmod($this->full_dst_path, FILE_WRITE_MODE);

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Image Process Using ImageMagick.
     *
     * This function will resize, crop or rotate
     *
     * @param	string
     *
     * @return bool
     */
    public function image_process_imagemagick($action = 'resize')
    {
        //  Do we have a vaild library path?
        if ($this->library_path == '') {
            $this->set_error('imglib_libpath_invalid');

            return false;
        }

        if (!preg_match('/convert$/i', $this->library_path)) {
            $this->library_path = rtrim($this->library_path, '/').'/';

            $this->library_path .= 'convert';
        }

        // Execute the command
        $cmd = $this->library_path.' -quality '.$this->quality;

        if ($action == 'crop') {
            $cmd .= ' -crop '.$this->width.'x'.$this->height.'+'.$this->x_axis.'+'.$this->y_axis." \"$this->full_src_path\" \"$this->full_dst_path\" 2>&1";
        } elseif ($action == 'rotate') {
            switch ($this->rotation_angle) {
                case 'hor': $angle = '-flop';
                    break;
                case 'vrt': $angle = '-flip';
                    break;
                default: $angle = '-rotate '.$this->rotation_angle;
                    break;
            }

            $cmd .= ' '.$angle." \"$this->full_src_path\" \"$this->full_dst_path\" 2>&1";
        } else {  // Resize
            $cmd .= ' -resize '.$this->width.'x'.$this->height." \"$this->full_src_path\" \"$this->full_dst_path\" 2>&1";
        }

        $retval = 1;

        @exec($cmd, $output, $retval);

        //	Did it work?
        if ($retval > 0) {
            $this->set_error('imglib_image_process_failed');

            return false;
        }

        // Set the file to 777
        @chmod($this->full_dst_path, FILE_WRITE_MODE);

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Image Process Using NetPBM.
     *
     * This function will resize, crop or rotate
     *
     * @param	string
     *
     * @return bool
     */
    public function image_process_netpbm($action = 'resize')
    {
        if ($this->library_path == '') {
            $this->set_error('imglib_libpath_invalid');

            return false;
        }

        //  Build the resizing command
        switch ($this->image_type) {
            case 1:
                        $cmd_in = 'giftopnm';
                        $cmd_out = 'ppmtogif';
                break;
            case 2:
                        $cmd_in = 'jpegtopnm';
                        $cmd_out = 'ppmtojpeg';
                break;
            case 3:
                        $cmd_in = 'pngtopnm';
                        $cmd_out = 'ppmtopng';
                break;
        }

        if ($action == 'crop') {
            $cmd_inner = 'pnmcut -left '.$this->x_axis.' -top '.$this->y_axis.' -width '.$this->width.' -height '.$this->height;
        } elseif ($action == 'rotate') {
            switch ($this->rotation_angle) {
                case 90:	$angle = 'r270';
                    break;
                case 180:	$angle = 'r180';
                    break;
                case 270:	$angle = 'r90';
                    break;
                case 'vrt':	$angle = 'tb';
                    break;
                case 'hor':	$angle = 'lr';
                    break;
            }

            $cmd_inner = 'pnmflip -'.$angle.' ';
        } else { // Resize
            $cmd_inner = 'pnmscale -xysize '.$this->width.' '.$this->height;
        }

        $cmd = $this->library_path.$cmd_in.' '.$this->full_src_path.' | '.$cmd_inner.' | '.$cmd_out.' > '.$this->dest_folder.'netpbm.tmp';

        $retval = 1;

        @exec($cmd, $output, $retval);

        //  Did it work?
        if ($retval > 0) {
            $this->set_error('imglib_image_process_failed');

            return false;
        }

        // With NetPBM we have to create a temporary image.
        // If you try manipulating the original it fails so
        // we have to rename the temp file.
        copy($this->dest_folder.'netpbm.tmp', $this->full_dst_path);
        unlink($this->dest_folder.'netpbm.tmp');
        @chmod($this->full_dst_path, FILE_WRITE_MODE);

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Image Rotate Using GD.
     *
     * @return bool
     */
    public function image_rotate_gd()
    {
        //  Create the image handle
        if (!($src_img = $this->image_create_gd())) {
            return false;
        }

        // Set the background color
        // This won't work with transparent PNG files so we are
        // going to have to figure out how to determine the color
        // of the alpha channel in a future release.

        $white = imagecolorallocate($src_img, 255, 255, 255);

        //  Rotate it!
        $dst_img = imagerotate($src_img, $this->rotation_angle, $white);

        //  Save the Image
        if ($this->dynamic_output == true) {
            $this->image_display_gd($dst_img);
        } else {
            // Or save it
            if (!$this->image_save_gd($dst_img)) {
                return false;
            }
        }

        //  Kill the file handles
        imagedestroy($dst_img);
        imagedestroy($src_img);

        // Set the file to 777

        @chmod($this->full_dst_path, FILE_WRITE_MODE);

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Create Mirror Image using GD.
     *
     * This function will flip horizontal or vertical
     *
     * @return bool
     */
    public function image_mirror_gd()
    {
        if (!$src_img = $this->image_create_gd()) {
            return false;
        }

        $width = $this->orig_width;
        $height = $this->orig_height;

        if ($this->rotation_angle == 'hor') {
            for ($i = 0; $i < $height; $i++) {
                $left = 0;
                $right = $width - 1;

                while ($left < $right) {
                    $cl = imagecolorat($src_img, $left, $i);
                    $cr = imagecolorat($src_img, $right, $i);

                    imagesetpixel($src_img, $left, $i, $cr);
                    imagesetpixel($src_img, $right, $i, $cl);

                    $left++;
                    $right--;
                }
            }
        } else {
            for ($i = 0; $i < $width; $i++) {
                $top = 0;
                $bot = $height - 1;

                while ($top < $bot) {
                    $ct = imagecolorat($src_img, $i, $top);
                    $cb = imagecolorat($src_img, $i, $bot);

                    imagesetpixel($src_img, $i, $top, $cb);
                    imagesetpixel($src_img, $i, $bot, $ct);

                    $top++;
                    $bot--;
                }
            }
        }

        //  Show the image
        if ($this->dynamic_output == true) {
            $this->image_display_gd($src_img);
        } else {
            // Or save it
            if (!$this->image_save_gd($src_img)) {
                return false;
            }
        }

        //  Kill the file handles
        imagedestroy($src_img);

        // Set the file to 777
        @chmod($this->full_dst_path, FILE_WRITE_MODE);

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Image Watermark.
     *
     * This is a wrapper function that chooses the type
     * of watermarking based on the specified preference.
     *
     * @param	string
     *
     * @return bool
     */
    public function watermark()
    {
        if ($this->wm_type == 'overlay') {
            return $this->overlay_watermark();
        } else {
            return $this->text_watermark();
        }
    }

    // --------------------------------------------------------------------

    /**
     * Watermark - Graphic Version.
     *
     * @return bool
     */
    public function overlay_watermark()
    {
        if (!function_exists('imagecolortransparent')) {
            $this->set_error('imglib_gd_required');

            return false;
        }

        //  Fetch source image properties
        $this->get_image_properties();

        //  Fetch watermark image properties
        $props = $this->get_image_properties($this->wm_overlay_path, true);
        $wm_img_type = $props['image_type'];
        $wm_width = $props['width'];
        $wm_height = $props['height'];

        //  Create two image resources
        $wm_img = $this->image_create_gd($this->wm_overlay_path, $wm_img_type);
        $src_img = $this->image_create_gd($this->full_src_path);

        // Reverse the offset if necessary
        // When the image is positioned at the bottom
        // we don't want the vertical offset to push it
        // further down.  We want the reverse, so we'll
        // invert the offset.  Same with the horizontal
        // offset when the image is at the right

        $this->wm_vrt_alignment = strtoupper(substr($this->wm_vrt_alignment, 0, 1));
        $this->wm_hor_alignment = strtoupper(substr($this->wm_hor_alignment, 0, 1));

        if ($this->wm_vrt_alignment == 'B') {
            $this->wm_vrt_offset = $this->wm_vrt_offset * -1;
        }

        if ($this->wm_hor_alignment == 'R') {
            $this->wm_hor_offset = $this->wm_hor_offset * -1;
        }

        //  Set the base x and y axis values
        $x_axis = $this->wm_hor_offset + $this->wm_padding;
        $y_axis = $this->wm_vrt_offset + $this->wm_padding;

        //  Set the vertical position
        switch ($this->wm_vrt_alignment) {
            case 'T':
                break;
            case 'M':	$y_axis += ($this->orig_height / 2) - ($wm_height / 2);
                break;
            case 'B':	$y_axis += $this->orig_height - $wm_height;
                break;
        }

        //  Set the horizontal position
        switch ($this->wm_hor_alignment) {
            case 'L':
                break;
            case 'C':	$x_axis += ($this->orig_width / 2) - ($wm_width / 2);
                break;
            case 'R':	$x_axis += $this->orig_width - $wm_width;
                break;
        }

        //  Build the finalized image
        if ($wm_img_type == 3 and function_exists('imagealphablending')) {
            @imagealphablending($src_img, true);
        }

        // Set RGB values for text and shadow
        $rgba = imagecolorat($wm_img, $this->wm_x_transp, $this->wm_y_transp);
        $alpha = ($rgba & 0x7F000000) >> 24;

        // make a best guess as to whether we're dealing with an image with alpha transparency or no/binary transparency
        if ($alpha > 0) {
            // copy the image directly, the image's alpha transparency being the sole determinant of blending
            imagecopy($src_img, $wm_img, $x_axis, $y_axis, 0, 0, $wm_width, $wm_height);
        } else {
            // set our RGB value from above to be transparent and merge the images with the specified opacity
            imagecolortransparent($wm_img, imagecolorat($wm_img, $this->wm_x_transp, $this->wm_y_transp));
            imagecopymerge($src_img, $wm_img, $x_axis, $y_axis, 0, 0, $wm_width, $wm_height, $this->wm_opacity);
        }

        //  Output the image
        if ($this->dynamic_output == true) {
            $this->image_display_gd($src_img);
        } else {
            if (!$this->image_save_gd($src_img)) {
                return false;
            }
        }

        imagedestroy($src_img);
        imagedestroy($wm_img);

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Watermark - Text Version.
     *
     * @return bool
     */
    public function text_watermark()
    {
        if (!($src_img = $this->image_create_gd())) {
            return false;
        }

        if ($this->wm_use_truetype == true and !file_exists($this->wm_font_path)) {
            $this->set_error('imglib_missing_font');

            return false;
        }

        //  Fetch source image properties
        $this->get_image_properties();

        // Set RGB values for text and shadow
        $this->wm_font_color = str_replace('#', '', $this->wm_font_color);
        $this->wm_shadow_color = str_replace('#', '', $this->wm_shadow_color);

        $R1 = hexdec(substr($this->wm_font_color, 0, 2));
        $G1 = hexdec(substr($this->wm_font_color, 2, 2));
        $B1 = hexdec(substr($this->wm_font_color, 4, 2));

        $R2 = hexdec(substr($this->wm_shadow_color, 0, 2));
        $G2 = hexdec(substr($this->wm_shadow_color, 2, 2));
        $B2 = hexdec(substr($this->wm_shadow_color, 4, 2));

        $txt_color = imagecolorclosest($src_img, $R1, $G1, $B1);
        $drp_color = imagecolorclosest($src_img, $R2, $G2, $B2);

        // Reverse the vertical offset
        // When the image is positioned at the bottom
        // we don't want the vertical offset to push it
        // further down.  We want the reverse, so we'll
        // invert the offset.  Note: The horizontal
        // offset flips itself automatically

        if ($this->wm_vrt_alignment == 'B') {
            $this->wm_vrt_offset = $this->wm_vrt_offset * -1;
        }

        if ($this->wm_hor_alignment == 'R') {
            $this->wm_hor_offset = $this->wm_hor_offset * -1;
        }

        // Set font width and height
        // These are calculated differently depending on
        // whether we are using the true type font or not
        if ($this->wm_use_truetype == true) {
            if ($this->wm_font_size == '') {
                $this->wm_font_size = '17';
            }

            $fontwidth = $this->wm_font_size - ($this->wm_font_size / 4);
            $fontheight = $this->wm_font_size;
            $this->wm_vrt_offset += $this->wm_font_size;
        } else {
            $fontwidth = imagefontwidth($this->wm_font_size);
            $fontheight = imagefontheight($this->wm_font_size);
        }

        // Set base X and Y axis values
        $x_axis = $this->wm_hor_offset + $this->wm_padding;
        $y_axis = $this->wm_vrt_offset + $this->wm_padding;

        // Set verticle alignment
        if ($this->wm_use_drop_shadow == false) {
            $this->wm_shadow_distance = 0;
        }

        $this->wm_vrt_alignment = strtoupper(substr($this->wm_vrt_alignment, 0, 1));
        $this->wm_hor_alignment = strtoupper(substr($this->wm_hor_alignment, 0, 1));

        switch ($this->wm_vrt_alignment) {
            case	 'T':
                break;
            case 'M':	$y_axis += ($this->orig_height / 2) + ($fontheight / 2);
                break;
            case 'B':	$y_axis += ($this->orig_height - $fontheight - $this->wm_shadow_distance - ($fontheight / 2));
                break;
        }

        $x_shad = $x_axis + $this->wm_shadow_distance;
        $y_shad = $y_axis + $this->wm_shadow_distance;

        // Set horizontal alignment
        switch ($this->wm_hor_alignment) {
            case 'L':
                break;
            case 'R':
                        if ($this->wm_use_drop_shadow) {
                            $x_shad += ($this->orig_width - $fontwidth * strlen($this->wm_text));
                        }
                            $x_axis += ($this->orig_width - $fontwidth * strlen($this->wm_text));
                break;
            case 'C':
                        if ($this->wm_use_drop_shadow) {
                            $x_shad += floor(($this->orig_width - $fontwidth * strlen($this->wm_text)) / 2);
                        }
                            $x_axis += floor(($this->orig_width - $fontwidth * strlen($this->wm_text)) / 2);
                break;
        }

        //  Add the text to the source image
        if ($this->wm_use_truetype) {
            if ($this->wm_use_drop_shadow) {
                imagettftext($src_img, $this->wm_font_size, 0, $x_shad, $y_shad, $drp_color, $this->wm_font_path, $this->wm_text);
            }
            imagettftext($src_img, $this->wm_font_size, 0, $x_axis, $y_axis, $txt_color, $this->wm_font_path, $this->wm_text);
        } else {
            if ($this->wm_use_drop_shadow) {
                imagestring($src_img, $this->wm_font_size, $x_shad, $y_shad, $this->wm_text, $drp_color);
            }
            imagestring($src_img, $this->wm_font_size, $x_axis, $y_axis, $this->wm_text, $txt_color);
        }

        //  Output the final image
        if ($this->dynamic_output == true) {
            $this->image_display_gd($src_img);
        } else {
            $this->image_save_gd($src_img);
        }

        imagedestroy($src_img);

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Create Image - GD.
     *
     * This simply creates an image resource handle
     * based on the type of image being processed
     *
     * @param	string
     *
     * @return resource
     */
    public function image_create_gd($path = '', $image_type = '')
    {
        if ($path == '') {
            $path = $this->full_src_path;
        }

        if ($image_type == '') {
            $image_type = $this->image_type;
        }

        switch ($image_type) {
            case	 1:
                        if (!function_exists('imagecreatefromgif')) {
                            $this->set_error(['imglib_unsupported_imagecreate', 'imglib_gif_not_supported']);

                            return false;
                        }

                        return imagecreatefromgif($path);
                break;
            case 2:
                        if (!function_exists('imagecreatefromjpeg')) {
                            $this->set_error(['imglib_unsupported_imagecreate', 'imglib_jpg_not_supported']);

                            return false;
                        }

                        return imagecreatefromjpeg($path);
                break;
            case 3:
                        if (!function_exists('imagecreatefrompng')) {
                            $this->set_error(['imglib_unsupported_imagecreate', 'imglib_png_not_supported']);

                            return false;
                        }

                        return imagecreatefrompng($path);
                break;

        }

        $this->set_error(['imglib_unsupported_imagecreate']);

        return false;
    }

    // --------------------------------------------------------------------

    /**
     * Write image file to disk - GD.
     *
     * Takes an image resource as input and writes the file
     * to the specified destination
     *
     * @param	resource
     *
     * @return bool
     */
    public function image_save_gd($resource)
    {
        switch ($this->image_type) {
            case 1:
                        if (!function_exists('imagegif')) {
                            $this->set_error(['imglib_unsupported_imagecreate', 'imglib_gif_not_supported']);

                            return false;
                        }

                        if (!@imagegif($resource, $this->full_dst_path)) {
                            $this->set_error('imglib_save_failed');

                            return false;
                        }
                break;
            case 2:
                        if (!function_exists('imagejpeg')) {
                            $this->set_error(['imglib_unsupported_imagecreate', 'imglib_jpg_not_supported']);

                            return false;
                        }

                        if (!@imagejpeg($resource, $this->full_dst_path, $this->quality)) {
                            $this->set_error('imglib_save_failed');

                            return false;
                        }
                break;
            case 3:
                        if (!function_exists('imagepng')) {
                            $this->set_error(['imglib_unsupported_imagecreate', 'imglib_png_not_supported']);

                            return false;
                        }

                        if (!@imagepng($resource, $this->full_dst_path)) {
                            $this->set_error('imglib_save_failed');

                            return false;
                        }
                break;
            default:
                            $this->set_error(['imglib_unsupported_imagecreate']);

                            return false;
                break;
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Dynamically outputs an image.
     *
     * @param	resource
     *
     * @return void
     */
    public function image_display_gd($resource)
    {
        header("Content-Disposition: filename={$this->source_image};");
        header("Content-Type: {$this->mime_type}");
        header('Content-Transfer-Encoding: binary');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', time()).' GMT');

        switch ($this->image_type) {
            case 1:	imagegif($resource);
                break;
            case 2:	imagejpeg($resource, '', $this->quality);
                break;
            case 3:	imagepng($resource);
                break;
            default:	echo 'Unable to display the image';
                break;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Re-proportion Image Width/Height.
     *
     * When creating thumbs, the desired width/height
     * can end up warping the image due to an incorrect
     * ratio between the full-sized image and the thumb.
     *
     * This function lets us re-proportion the width/height
     * if users choose to maintain the aspect ratio when resizing.
     *
     * @return void
     */
    public function image_reproportion()
    {
        if (!is_numeric($this->width) or !is_numeric($this->height) or $this->width == 0 or $this->height == 0) {
            return;
        }

        if (!is_numeric($this->orig_width) or !is_numeric($this->orig_height) or $this->orig_width == 0 or $this->orig_height == 0) {
            return;
        }

        $new_width = ceil($this->orig_width * $this->height / $this->orig_height);
        $new_height = ceil($this->width * $this->orig_height / $this->orig_width);

        $ratio = (($this->orig_height / $this->orig_width) - ($this->height / $this->width));

        if ($this->master_dim != 'width' and $this->master_dim != 'height') {
            $this->master_dim = ($ratio < 0) ? 'width' : 'height';
        }

        if (($this->width != $new_width) and ($this->height != $new_height)) {
            if ($this->master_dim == 'height') {
                $this->width = $new_width;
            } else {
                $this->height = $new_height;
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Get image properties.
     *
     * A helper function that gets info about the file
     *
     * @param	string
     *
     * @return mixed
     */
    public function get_image_properties($path = '', $return = false)
    {
        // For now we require GD but we should
        // find a way to determine this using IM or NetPBM

        if ($path == '') {
            $path = $this->full_src_path;
        }

        if (!file_exists($path)) {
            $this->set_error('imglib_invalid_path');

            return false;
        }

        $vals = @getimagesize($path);

        $types = [1 => 'gif', 2 => 'jpeg', 3 => 'png'];

        $mime = (isset($types[$vals['2']])) ? 'image/'.$types[$vals['2']] : 'image/jpg';

        if ($return == true) {
            $v['width'] = $vals['0'];
            $v['height'] = $vals['1'];
            $v['image_type'] = $vals['2'];
            $v['size_str'] = $vals['3'];
            $v['mime_type'] = $mime;

            return $v;
        }

        $this->orig_width = $vals['0'];
        $this->orig_height = $vals['1'];
        $this->image_type = $vals['2'];
        $this->size_str = $vals['3'];
        $this->mime_type = $mime;

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Size calculator.
     *
     * This function takes a known width x height and
     * recalculates it to a new size.  Only one
     * new variable needs to be known
     *
     *	$props = array(
     *					'width'			=> $width,
     *					'height'		=> $height,
     *					'new_width'		=> 40,
     *					'new_height'	=> ''
     *				  );
     *
     * @param	array
     *
     * @return array
     */
    public function size_calculator($vals)
    {
        if (!is_array($vals)) {
            return;
        }

        $allowed = ['new_width', 'new_height', 'width', 'height'];

        foreach ($allowed as $item) {
            if (!isset($vals[$item]) or $vals[$item] == '') {
                $vals[$item] = 0;
            }
        }

        if ($vals['width'] == 0 or $vals['height'] == 0) {
            return $vals;
        }

        if ($vals['new_width'] == 0) {
            $vals['new_width'] = ceil($vals['width'] * $vals['new_height'] / $vals['height']);
        } elseif ($vals['new_height'] == 0) {
            $vals['new_height'] = ceil($vals['new_width'] * $vals['height'] / $vals['width']);
        }

        return $vals;
    }

    // --------------------------------------------------------------------

    /**
     * Explode source_image.
     *
     * This is a helper function that extracts the extension
     * from the source_image.  This function lets us deal with
     * source_images with multiple periods, like:  my.cool.jpg
     * It returns an associative array with two elements:
     * $array['ext']  = '.jpg';
     * $array['name'] = 'my.cool';
     *
     * @param	array
     *
     * @return array
     */
    public function explode_name($source_image)
    {
        $ext = strrchr($source_image, '.');
        $name = ($ext === false) ? $source_image : substr($source_image, 0, -strlen($ext));

        return ['ext' => $ext, 'name' => $name];
    }

    // --------------------------------------------------------------------

    /**
     * Is GD Installed?
     *
     * @return bool
     */
    public function gd_loaded()
    {
        if (!extension_loaded('gd')) {
            if (!dl('gd.so')) {
                return false;
            }
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Get GD version.
     *
     * @return mixed
     */
    public function gd_version()
    {
        if (function_exists('gd_info')) {
            $gd_version = @gd_info();
            $gd_version = preg_replace("/\D/", '', $gd_version['GD Version']);

            return $gd_version;
        }

        return false;
    }

    // --------------------------------------------------------------------

    /**
     * Set error message.
     *
     * @param	string
     *
     * @return void
     */
    public function set_error($msg)
    {
        $CI = &get_instance();
        $CI->lang->load('imglib');

        if (is_array($msg)) {
            foreach ($msg as $val) {
                $msg = ($CI->lang->line($val) == false) ? $val : $CI->lang->line($val);
                $this->error_msg[] = $msg;
                log_message('error', $msg);
            }
        } else {
            $msg = ($CI->lang->line($msg) == false) ? $msg : $CI->lang->line($msg);
            $this->error_msg[] = $msg;
            log_message('error', $msg);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Show error messages.
     *
     * @param	string
     *
     * @return string
     */
    public function display_errors($open = '<p>', $close = '</p>')
    {
        $str = '';
        foreach ($this->error_msg as $val) {
            $str .= $open.$val.$close;
        }

        return $str;
    }
}
// END Image_lib Class

/* End of file Image_lib.php */
/* Location: ./system/libraries/Image_lib.php */
