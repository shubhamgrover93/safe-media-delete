<?php 

/*
* Plugin Name: Safe Media Delete
* Description: Safe Mdia Delete
* Version: 1.0
* Author: Shubham
* Author URI: Shubham
*/

/**
 * Plugin class
 **/
if ( ! class_exists( 'CT_TAX_META' ) ) {

class CT_TAX_META {

  public function __construct() {
    //
  }
 
 /*
  * Initialize the class and start calling our hooks and filters
  * @since 1.0.0
 */
 public function init() {
   add_action( 'category_add_form_fields', array ( $this, 'add_category_image' ), 10, 2 );
   add_action( 'created_category', array ( $this, 'save_category_image' ), 10, 2 );
   add_action( 'category_edit_form_fields', array ( $this, 'update_category_image' ), 10, 2 );
   add_action( 'edited_category', array ( $this, 'updated_category_image' ), 10, 2 );
   add_action( 'admin_enqueue_scripts', array( $this, 'load_media' ) );
   add_action( 'admin_footer', array ( $this, 'add_script' ) );
 }

public function load_media() {
 wp_enqueue_media();
}
 
 /*
  * Add a form field in the new category page
  * @since 1.0.0
 */
 public function add_category_image ( $taxonomy ) { ?>
 
   <div class="form-field term-group">
     <label for="category-image-id"><?php _e('Image', 'hero-theme'); ?></label>
     <input type="hidden" id="category-image-id" name="category-image-id" class="custom_media_url" value="">
     <div id="category-image-wrapper"></div>
     <p>
       <input type="button" class="button button-secondary ct_tax_media_button" id="ct_tax_media_button" name="ct_tax_media_button" value="<?php _e( 'Add Image', 'hero-theme' ); ?>" />
       <input type="button" class="button button-secondary ct_tax_media_remove" id="ct_tax_media_remove" name="ct_tax_media_remove" value="<?php _e( 'Remove Image', 'hero-theme' ); ?>" />
    </p>
   </div>
 <?php
 }
 /*
  * Save the form field
  * @since 1.0.0
 */
 public function save_category_image ( $term_id, $tt_id ) {
   if( isset( $_POST['category-image-id'] ) && '' !== $_POST['category-image-id'] ){
     $image = $_POST['category-image-id'];
     add_term_meta( $term_id, 'category-image-id', $image, true );
   }
 }
 
 /*
  * Edit the form field
  * @since 1.0.0
 */
 public function update_category_image ( $term, $taxonomy ) { ?>
   <tr class="form-field term-group-wrap">
     <th scope="row">
       <label for="category-image-id"><?php _e( 'Image', 'hero-theme' ); ?></label>
     </th>
     <td>
       <?php $image_id = get_term_meta ( $term -> term_id, 'category-image-id', true ); ?>
       <input type="hidden" id="category-image-id" name="category-image-id" value="<?php echo $image_id; ?>">
       <div id="category-image-wrapper">
         <?php if ( $image_id ) { ?>
           <?php echo wp_get_attachment_image ( $image_id, 'thumbnail' ); ?>
         <?php } ?>
       </div>
       <p>
         <input type="button" class="button button-secondary ct_tax_media_button" id="ct_tax_media_button" name="ct_tax_media_button" value="<?php _e( 'Add Image', 'hero-theme' ); ?>" />
         <input type="button" class="button button-secondary ct_tax_media_remove" id="ct_tax_media_remove" name="ct_tax_media_remove" value="<?php _e( 'Remove Image', 'hero-theme' ); ?>" />
       </p>
     </td>
   </tr>
 <?php
 }

/*
 * Update the form field value
 * @since 1.0.0
 */
 public function updated_category_image ( $term_id, $tt_id ) {
   if( isset( $_POST['category-image-id'] ) && '' !== $_POST['category-image-id'] ){
     $image = $_POST['category-image-id'];
     update_term_meta ( $term_id, 'category-image-id', $image );
   } else {
     update_term_meta ( $term_id, 'category-image-id', '' );
   }
 }

/*
 * Add script
 * @since 1.0.0
 */
 public function add_script() { ?>
   <script>
     jQuery(document).ready( function($) {
       function ct_media_upload(button_class) {
         var _custom_media = true,
         _orig_send_attachment = wp.media.editor.send.attachment;
         $('body').on('click', button_class, function(e) {
           var button_id = '#'+$(this).attr('id');
           var send_attachment_bkp = wp.media.editor.send.attachment;
           var button = $(button_id);
           _custom_media = true;
           wp.media.editor.send.attachment = function(props, attachment){
             if ( _custom_media ) {
               $('#category-image-id').val(attachment.id);
               $('#category-image-wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
               $('#category-image-wrapper .custom_media_image').attr('src',attachment.url).css('display','block');
             } else {
               return _orig_send_attachment.apply( button_id, [props, attachment] );
             }
            }
         wp.media.editor.open(button);
         return false;
       });
     }
     ct_media_upload('.ct_tax_media_button.button'); 
     $('body').on('click','.ct_tax_media_remove',function(){
       $('#category-image-id').val('');
       $('#category-image-wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
     });

     $(document).ajaxComplete(function(event, xhr, settings) {
       var queryStringArr = settings.data.split('&');
       if( $.inArray('action=add-tag', queryStringArr) !== -1 ){
         var xml = xhr.responseXML;
         $response = $(xml).find('term_id').text();
         if($response!=""){
           // Clear the thumb image
           $('#category-image-wrapper').html('');
         }
       }
     });
   });
 </script>
 <?php }

  }
 
$CT_TAX_META = new CT_TAX_META();
$CT_TAX_META -> init();
 
}

 /*
  * Prevet Used Media File 
 */

function prevent_media_library_deletion( $return, $post) {
	$post_id = $post->ID;
	$attached_to = get_media_attachments ( $post_id );
	if ( ! empty ( $attached_to ) ) {
		return false;

	}
}
add_filter ( 'pre_delete_attachment', 'prevent_media_library_deletion', 99, 2 );

 /*
  * Code to Add Linked Object.  Section in the Media Library
 */

function add_attachment_columns($columns) {
    $columns['attached_to'] = __('Linked Object');
    return $columns;
}

add_filter('manage_media_columns', 'add_attachment_columns');

 /*
  * Code to Add Post/Term Id  to Media Image  under  Linked Object.  
 */

function get_media_attachments ( $media_id ) {
	$attached_to = array();

		$posts = get_posts(array(
			'post_type' => 'any',
			'meta_query' => array(
				array(
					'key' => '_thumbnail_id',
					'value' => $media_id,
					'compare' => '='
				)
			),
		));
		if ($posts) {
			foreach ($posts as $post) {
				$attached_to[] = array(
					'id' => $post->ID,
					'type' => $post->post_type
				);
			}
		}
	
	global $wpdb;
	$keyword = '"id":' . $media_id;
	$posts = $wpdb->get_results ( "SELECT `ID`, `post_type` FROM `{$wpdb->prefix}posts` WHERE ( `post_content` LIKE '%" . $keyword . ",%' OR `post_content` LIKE '%" . $keyword . "}%' ) AND `post_type` NOT IN ('revision')" );
	if ($posts) {
		foreach ($posts as $post) {
			$attached_to[] = array(
				'id' => $post->ID,
				'type' => $post->post_type
			);
		}
	}
	
	
		$terms	= get_terms ( array(
			//'taxonomy'	=> $taxonomy->taxonomy,
			'hide_empty' => false,
			'meta_query' => array (
				array(
					'key'     => 'category-image-id',
					'value'   => $media_id
				)
			)
		) );
		
		if ( ! empty ( $terms ) ) {
			foreach ($terms as $term) {
				$attached_to[] = array(
					'id' => $term->term_id,
					'type' => 'term',
					'taxonomy' => $term->taxonomy
				);
			}
		}
		
	return $attached_to;
}


function add_attachment_column_content($column_name, $post_id) {
    if ($column_name == 'attached_to') {
        $attached_to = get_media_attachments ( $post_id );
		
        if ($attached_to) {
            foreach ($attached_to as $key => $item) {
                $id = $item['id'];
                $type = $item['type'];
                $edit_link = '';
                if ($type == 'post') {
                    $edit_link = get_edit_post_link($id);
                } elseif ($type == 'term') {
                    $taxonomy = $item['taxonomy'];
                    $term = get_term($id, $taxonomy);
                    $edit_link = get_edit_term_link($term->term_id, $taxonomy);
                }
                $attached_to[$key]['edit_link'] = $edit_link;
            }
            $attached_to = array_map(function ($item) {
                $id = $item['id'];
                $type = $item['type'];
                $edit_link = $item['edit_link'];
                $label = $type == 'post' ? '' : 'Term';
                $id_label = $type == 'post' ? '#' : '';
                return '<a href="' . $edit_link . '">' . $label . ' ' . $id_label . $id . '</a>';
            }, $attached_to);
            echo implode(', ', $attached_to);
        } else {
            echo '-';
        }
    }
}
add_action('manage_media_custom_column', 'add_attachment_column_content', 10, 2);

// Add a "Linked Article" field to the attachment details screen popup

function add_linked_article_field_to_attachment_details_popups( $form_fields, $post ) {
	ob_start ();
    add_attachment_column_content ( 'attached_to', $post->ID );
	$html = ob_get_clean();
	
	$form_fields['linked_article'] = array(
        'label' => __( 'Linked Article' ),
        'input' => 'html',
        'html'  => $html,
    );
	
    return $form_fields;
}
add_filter( 'attachment_fields_to_edit', 'add_linked_article_field_to_attachment_details_popups', 10, 2 );

//Api for delete media
function deleteMedia( $request) {
  $attached_to = get_media_attachments ( $request['id'] );
    if ( ! empty ( $attached_to ) ) {
   $message['success'] = false;
   $message['status'] = 406;
   $message['message'] = 'Sorry, Media somewhere used';
   return $message;
    }else{
    add_filter ( 'pre_delete_attachment', 'deleteMedia', 99, 2 );
    $message['success'] = true;
    $message['status'] = 200;
    $message['message'] = 'Media deleted successfully';
    return $message;
  }
}
add_action( 'rest_api_init', function () {
  register_rest_route( 'safe-media-delete/v1', '/delete/(?P<id>\d+)', array(
    'methods' => 'GET',
    'callback' => 'deleteMedia',
  ) );

} );

// Api for get media
function getMedia($request){
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, get_site_url().'/wp-json/wp/v2/media/'.$request['id']);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $media = curl_exec ($ch);
  curl_close ($ch);

  $media = json_decode($media, true);
  $attached_to = get_media_attachments ( $request['id'] );
  $data['id'] = $media['id'];
  $data['date'] = $media['date'];
  $data['slug'] = $media['slug'];
  $data['type'] = $media['mime_type'];
  $data['alt_text'] = $media['alt_text'];
  $data['link'] = $media['link'];
  $data['attached_objects'] = $attached_to;
  return $data;

}

add_action( 'rest_api_init', function () {
  register_rest_route( 'safe-media-delete/v1', '/get/(?P<id>\d+)', array(
    'methods' => 'GET',
    'callback' => 'getMedia',
  ) );

} );
