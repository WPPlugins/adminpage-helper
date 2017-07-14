<?php
/*
Plugin Name: admin page Helper
Description: Some function to help developer to write admin page e panel   
Plugin URI:  http://www.decristofano.it/
Version:     0.4
Author:      lucdecri
Author URI:  http://www.decristofano.it/
*/

define('ADMIN_HELPER','0.4');

function ah_first() {
	// ensure path to this file is via main wp plugin path
	$wp_path_to_this_file = preg_replace('/(.*)plugins\/(.*)$/', WP_PLUGIN_DIR."/$2", __FILE__);
	$this_plugin = plugin_basename(trim($wp_path_to_this_file));
	$active_plugins = get_option('active_plugins');
	$this_plugin_key = array_search($this_plugin, $active_plugins);
	if ($this_plugin_key) { // if it's 0 it's the first plugin already, no need to continue
		array_splice($active_plugins, $this_plugin_key, 1);
		array_unshift($active_plugins, $this_plugin);
		update_option('active_plugins', $active_plugins);
	}
}



function admin_menu($parent, $page_title, $menu_title, $function_name, $menu_slug, $position='', $capability='edit_plugins', $icon_url='') {
	// add a panel in wordpres menu
	
	
	switch ($parent) {
		case '' : $parent = ''; break;
		case 'Dashboard': $parent='index.php'; break;
		case 'Posts': $parent='edit.php'; break;
		case 'Media': $parent='upload.php'; break;
		case 'Links': $parent='link-manager.php'; break;
		case 'Pages': $parent='edit.php?post_type=page'; break;
		case 'Comments': $parent='edit-comments.php'; break;
		case 'Appearance': $parent='themes.php'; break;
		case 'Plugins': $parent='plugins.php'; break;
		case 'Users': $parent='users.php'; break;
		case 'Tools': $parent='tools.php'; break;
		case 'Settings': $parent='options-general.php'; break;
	}
	
	if ($parent=='') 
		add_menu_page   (          $page_title, $menu_title, $capability, $menu_slug, $function_name, $icon_url, $position );
	else
		add_submenu_page( $parent, $page_title, $menu_title, $capability, $menu_slug, $function_name ); 
}

function admin_panel($name, $action, $title, $description, $info='', $localization='') {
  // create a form for admin panel
    echo '<div class="wrap">
    		<h2>'.$title.'</h2>
    		<h5>'.$info.'</h5>
    		<p>'.$description.'</p>
			<p><form name="'. $name.'" action="'.$action.'" method="post" id="'.$name.'">
			<fieldset>
    			<div class="UserOption">
   					<input type="hidden" name="page" value="'.$name.'" />';
}

function admin_field($id, $type, $text, $default, $localization='', $message='', $return=false ) {
  // add a field in form for admin panel
  //   type is the field type :
  //      littlenumber
  //      text
  //      color
  //      page : a page-break in the admin panel
  //      longtext
  //	  checkbox
  //	  hidden
  //	  button
  //	  readonly
  //	  date
  //	  file
	
	
    @list($text,$text2) = @explode('|',$text,2);
    $string = '<div class="field_wrapper form-field">';
    switch($type) {
	case 'break':
	    $string.= '<p></p>';
	break;
        case 'color':
        	$string.= '
          <label for="'.$id.'" class="label">'.__($text,$localization).'</label>
          #<input type="text" id="'.$id.'" maxlength="6" name="'.$id.'" value="'.$default.'"
	      size="6" onchange="ChangeColor(\''.$id.'_box\',this.value)" onkeyup="ChangeColor(\''.$id.'_box\',this.value)"/>
	      <span id="'.$id.'_box" style="background:#'.$default.'">&nbsp;&nbsp;&nbsp;</span>
	      <p>'.__($message,$localization).'</p>';
        break;
	case 'date':
	       $default= date_create_from_format('Y-m-d',$default);
	       if(!$default) $default=date_create();
		$default= $default->format('d-m-Y');
		$string.= '
		<label for="'.$id.'" class="label">'.__($text,$localization).'</label>
		<input type="text" name="'.$id.'" id="'.$id.'" value="'.$default.'" class="Datepicker"/>
		<p>'.__($message,$localization).'</p>
		';
	break;
	case 'file':
	    $string.= '<label for="'.$id.'">'.__($text,$localization).'</label>
		  <div class="file-input"><input id="'.$id.'" class="async-upload" type="file" name="'.$id.'" /></div>
		  <p>'.__($message,$localization).'</p>';
	break;
	case 'files':
	    $string.= '<label for="'.$id.'[]">'.__($text,$localization).'</label>
		<div class="new-files">
		    <div class="file-input"><input id="'.$id.'[]" class="async-upload" type="file" name="'.$id.'[]" /></div>
		    <a class="admin-add-file" href="javascript:void(0)">' . _('Add more file') . '</a>
		</div>
		<p>'.__($message,$localization).'</p>';
	    $string.= '<script type="text/javascript">
		jQuery(document).ready(function($) {
			// add more file
			$(".admin-add-file").click(function(){
				var $first = $(this).parent().find(".file-input:first");
				$first.clone().insertAfter($first).show();
				return false;
			});
		});
		</script>';
	break;

        case 'page':
		$string.= '
             	</div>
            	</fieldset>
	            <br />
                <fieldset>
	            <legend><b>'.__($text,$localization).'</b></legend>
	            <div class="Option">
                <p><i>'.__($message,$localization).'</i></p>';
        break;
        case 'select':
		$string.= '
		<label for="'.$id.'" class="label">'.__($text,$localization).'</label>
          <select class="field_select" id="'.$id.'" name="'.$id.'" >';
          	$options = explode(",",$text2);
          	foreach($options as $opt) {
				$v=$opt;$k=$opt;
				
          			if (strpos($opt,':')) list($k,$v)=explode(':',$opt,2);
				if ($k=='') $k=$v;
				
				if ($default==$k)	$d = ' selected="selected" ';
          			else			$d = ' ';
				
          			$string.= "<option value='$k' $d >".__($v,$localization)."</option>";
          	}
          $string.= '
          </select>
          <p>'.__($message,$localization).'</p>';
        break;
        case 'littlenumber':
	case 'smallesttext':
	  $string.= '
          <label class="label" for="'.$id.'">'.__($text,$localization).'</label><input type="text" maxlength="5" name="'.$id.'" value="'.$default.'" size="3" /> '.__($text2,$localization).'<br />
	   <p>'.__($message,$localization).'</p>';
        break;
        case 'smalltext':
	  $string.= '<label class="label" for="'.$id.'">'.__($text,$localization).'</label>
          <input type="text" maxlength="100" name="'.$id.'" id="'.$id.'" value="'.$default.'" size="20" /> '.__($text2,$localization).'<br />
	  <p>'.__($message,$localization).'</p>';
        break;
        case 'text':
          $string.= '
          <label class="label" for="'.$id.'">'.__($text,$localization).'</label>
          <input type="text" maxlength="300" name="'.$id.'" id="'.$id.'" value="'.$default.'" size="60" />'.__($text2,$localization).'<br />
	  <p>'.__($message,$localization).'</p>';
        break;
        case 'hidden':
          $string.= '<input type="hidden" name="'.$id.'" id="'.$id.'" value="'.$default.'" />';
        break;
        case 'button':
        	// @TODO button non funziona
           $string.= '<input type="button" name="'.$id.'" id="'.$id.'" value="'.$text.'" />';
        break;
        case 'longtext':
		if ($text!='') 
			$string.= '<label for="'.$id.'" class="label">'.__($text,$localization).'</label><br />';
		$string.= '<textarea  name="'.$id.'" id="'.$id.'" cols="60" rows="5" style="width:99%">'.$default.'</textarea> '.__($text2,$localization).'<br />
			<p>'.__($message,$localization).'</p>';
        break;
       case 'readonly':
	  $size = strlen($default);  
          $string.= '
          <label class="label" for="'.$id.'">'.__($text,$localization).'</label>
	  <input type="hidden" name="'.$id.'" id="'.$id.'" value="'.$default.'" />
	  <span style="color:darkgray; font-family: Consolas,Monaco,monospace; font-style: italic;border: solid 1px; background: none repeat scroll 0 0 #EAEAEA;">&nbsp;'.$default.'&nbsp;</span>'.__($text2,$localization).'<br />
          <p>'.__($message,$localization).'</p>';
        break;
        case 'checkbox':
		$string.= '<label for="'.$id.'" class="label">'.__($text,$localization).'</label>
				<input type="checkbox" name="'.$id.'" id="'.$id.'" ';
		if($default == '1') { $string.= ' checked="checked" '; }
		$string.= ' /><br /><p>'.__($message,$localization).'</p>';
        break;
    }
    $string.= '</div>';
    
    if ($return) return $string;
    else	 echo   $string;
}

function admin_field_save($name, $val=null) {
// save option named $name, if $val is defined
	$old = get_option($name);
	if ($val!=null) {
		if (get_option($name,'')!='') {
			update_option($name,$val);
		} else {
			add_option($name,$val,'',true);
		}
	}
}

function admin_field_save_post_meta($id,$name,$value) {
// save post meta named $name. Create new if don't exist, overwrite if exist
  if (!get_post_meta($id,$name)) 
        add_post_meta($id,$name,$value,true);
  else 
        update_post_meta($id,$name,$value);
}

function admin_field_save_post_meta_multiple($id,$name,$value) {
// save post meta named $name. Create new if don't exist, add new if exist
        add_post_meta($id,$name,$value,true);
}


function admin_table($columns_name) {
// create a table with specified columns
	echo '<table class="wp-list-table widefat fixed users" cellspacing="0">
		<thead>
			<tr>';
	foreach($columns_name as $col) echo '	<th>'.$col.'</th>';
	echo '		</thead>
		<tbody>';

}

function admin_table_row($row) {
// add row to table
	echo '<tr>';
	foreach($row as $td) echo '	<td>'.$td.'</td>';
	echo '</tr>';
}

function admin_table_close($columns_name) {
// add footer to table and close it
	echo '			</tbody>
				<tfoot>';
	foreach($columns_name as $col) echo '	<th>'.$col.'</th>';
	echo '</tfoot>
	     </table>';
}

function admin_panel_close() {
  // close a form of admin panel
  //@TODO date non funziona : datepicker è caricato? come lo personalizzo?
  echo '
    </fieldset>
    '.submit_button().'	
    </form>
    </p>
    <script>
	function ChangeColor(id,color) {
		jQuery(id).css("background-color","#"+color);
	}
	$(function() {
		$( ".Datepicker" ).datepicker();
		
	});	
	</script>

</div>
  ';
}

// ritorna i files in una cartella
function admin_scandirectory( $dirname = '.' ) { 
		$files = array(); 
		if( $handle = opendir( $dirname ) ) { 
			while( false !== ( $file = readdir( $handle ) ) ) {
				$info = pathinfo( $file );
                if ( isset($info['extension']) )
					   $files[] = utf8_encode( $file );
			}		
			closedir( $handle ); 
		} 
		sort( $files );
		return ( $files ); 
} 

function admin_fileinformation( $name ) {
		
		//Sanitizes a filename replacing whitespace with dashes
		$name = sanitize_file_name($name);
		
		//get the parts of the name
		$filepart = pathinfo ( strtolower($name) );
		
		if ( empty($filepart) )
			return false;
		
		// required until PHP 5.2.0
		if ( empty($filepart['filename']) ) 
			$filepart['filename'] = substr($filepart['basename'],0 ,strlen($filepart['basename']) - (strlen($filepart['extension']) + 1) );
		
		$filepart['filename'] = sanitize_title_with_dashes( $filepart['filename'] );
		
		//extension jpeg will not be recognized by the slideshow, so we rename it
		$filepart['extension'] = ($filepart['extension'] == 'jpeg') ? 'jpg' : $filepart['extension'];
		
		//combine the new file name
		$filepart['basename'] = $filepart['filename'] . '.' . $filepart['extension'];
		
		return $filepart;
}



function admin_widget($slug,$title,$widget_form,$widget_save) {
 // create a admin widget
 
 // alias
 wp_add_dashboard_widget($slug, $title, $widget_form,$widget_save);	

 // hook with add_action('wp_dashboard_setup', 'function_name' );

	
}


// è true se la pagina ha l'url specificato
function admin_check_url($url='') {
	if (!$url) return;
	
	$_REQUEST_URI = explode('?', $_SERVER['REQUEST_URI']);
	$url_len 	= strlen($url);
	$url_offset = $url_len * -1;
	// If out test string ($url) is longer than the page URL. skip
	if (strlen($_REQUEST_URI[0]) < $url_len) return;
	if ($url == substr($_REQUEST_URI[0], $url_offset, $url_len))
			return true;
}

// inserisce un termine di tassonomia, specificando anche custom field
function admin_insert_term($term,$taxonomy,$args) {
       
       wp_insert_term($term,$taxonomy,$args);       
       unset($args['description'],$args['parent'],$args['slug']);
       $data = serialize($args);
       $name = 'field_'.$taxonomy.'_'.$term;
       if (get_option($name,'')!='') {
		update_option($name,$data);
       } else {
		add_option($name,$data,'',true);
       }
}

// ritorna il valore di un termine di tassonomia
function admin_get_term_field_value($field,$term,$taxonomy) {
		$name = 'field_'.$taxonomy.'_'.$term;
		$args = unserialize(get_option($name));
		return $args[$field];
}
// ritorna tutti i field di una tassonomia
function admin_get_term_fields($term,$taxonomy) {
		$name = 'field_'.$taxnomomy.'_'.$term;
		$args = unserialize(get_option($name));
		return array_keys($args);
}

function admin_debug($var,$name='') {
    global $admin_debug_data;
	if (function_exists('dbgx_trace_var')) {
		if ($name=='') dbgx_trace_var( $var );
		else		dbgx_trace_var( $var,$name );
	} else {
	    $bt = debug_backtrace();
	    $refer = $bt[0]['file']."@".$bt[0]['line'];
	    $string = print_r($var,true);
	    $admin_debug_data[]=array(
		    'name' => $name, "var" => $string, "type" => 'info', "time" => microtime(true), 'refer' => $refer
		    );
	}
}

function admin_set_post_meta($id,$name,$value) {
    set_post_meta($id,$name,$value);
}

// funzione utile
function set_post_meta($id,$name,$value) {
  if (!get_post_meta($id,$name)) 
        add_post_meta($id,$name,$value,true);
  else 
        update_post_meta($id,$name,$value);
}

// add meta field to taxonomy
function add_taxonomy_meta($term,$taxonomy,$name,$value) {
       $name = 'field_'.$taxonomy.'_'.$term;
       $data = get_option($name,'');
       if ($data=='') return;
       $data[$name]=$value;
       update_option($name,$data);
}


// add custom fields for taxonomy user interface
function register_taxonomy_fields($taxonomy,$fields) {
    global $admin_taxonomy_data;
    
    $admin_taxonomy_data[$taxonomy]= $fields;
       // aggiungo gli hook alla tassonomia $taxonomy
    add_action($taxonomy.'_add_form_fields', 'ah_taxonomy_add');
    add_action($taxonomy.'_edit_form_fields', 'ah_taxonomy_edit');
    add_action('edited_'.$taxonomy, 'ah_taxonomy_save');
    add_action('created_'.$taxonomy, 'ah_taxonomy_save');
    add_action('get_'.$taxonomy,'ah_taxonomy_get',1,2);
    
}


// aggiunge i campi alla taxonomia, quando edito la tassonomia
function ah_taxonomy_edit($term) {
    global $admin_taxonomy_data;
    $taxonomy = $term->taxonomy;
    foreach($admin_taxonomy_data[$taxonomy] as $meta => $data) {
	admin_field($meta,$data['type'],$data['label'],$term->$meta,'',$data['message']);
    }
/*
     echo '<tr class="form-field">
            <th valign="top" scope="row">
            <label for="disabilita">Disabilita</label>
            </th>
            <td>
                <input id="disabilita" type="text"  size="40" value="'.$term->disabled.'" name="disabilita">
                <p class="description">Campi disabilitati nel post (id separati da virgole).</p>
            </td>
        </tr>';
    echo '<tr class="form-field">
            <th valign="top" scope="row">
            <label for="nascondi">Nascondi</label>
            </th>
            <td>
                <input id="nascondi" type="text"  size="40" value="'.$term->hidden.'" name="nascondi">
                <p class="description">Campi disabilitati nel post (id separati da virgole).</p>
            </td>
        </tr>';
    echo '<tr class="form-field">
            <th valign="top" scope="row">
            <label for="ordinamento">Campo di ordinamento</label>
            </th>
            <td>
                <input id="ordinamento" type="text"  size="40" value="'.$term->orderfield.'" name="ordinamento">
                <p class="description">Indica rispetto quale campo effettuare l\'ordinamento.</p>
            </td>
        </tr>';
*/


}


// aggiunge i campi alla tassonomia organization, quando la creo nella finestra
function ah_taxonomy_add($taxonomy) {
    global $admin_taxonomy_data;

    foreach($admin_taxonomy_data[$taxonomy] as $meta => $data) {
	admin_field($meta,$data['type'],$data['label'],'','',$data['message']);
    }
}

// salva i dati della taxonomia 
function ah_taxonomy_save( $term_id ) {
    global $admin_taxonomy_data;

    // tutti i controlli sono già stati fatti, o almeno dovrebbe essere così
    $term = get_term_by('id',$term_id,$_POST['taxonomy']);
    $slug = $term->slug;
    $taxonomy = $term->taxonomy;

    $args=array();
    foreach($admin_taxonomy_data[$taxonomy] as $meta => $data) {
	$args[$meta]=$_POST[$meta];
    }    
    
    // definisco il nome e serializzo l'array
    $data = serialize($args);
    $name = 'field_'.$taxonomy.'_'.$slug;
    if (get_option($name,'')!='') {
	    update_option($name,$data);
    } else {
	    add_option($name,$data,'',true);
    }
    
}



// modifica l'oggetto term così contiene anche i custom field
function ah_taxonomy_get($term,$taxonomy) {
    global $admin_taxonomy_data;

    // deserializzo i dati
    
    $name = 'field_'.$term->taxonomy.'_'.$term->slug;
    $args = unserialize(get_option($name));
    if (is_array($args))   foreach($args as $meta=>$value) $term->$meta = $value;
    
    return $term;
}

function ah_taxonomy_get_terms($terms, $id, $taxonomy) {
    
    foreach($terms as $term_id => $term) {
        $terms[$term_id] = ah_taxonomy_get($term,$taxonomy);
    }
        
    return $terms;
    
}

function register_attachment_fields($form_field) {
global $admin_attachment_data;
    foreach($form_field as $k=>$d)
	    $admin_attachment_data[$k]=$d;
}

// registra un box per un posttype
function register_post_box($posttype,$boxname,$description,$position,$priority,$form_fields) {
global $admin_post_data;
global $admin_boxes_data;
    
     foreach($form_fields as $k=>$d)
	    $admin_post_data[$posttype][$boxname][$k]=$d;
    $admin_boxes_data[$boxname]= array(
			    'type' => $posttype,
			    'description' => $description,
			    'position' => $position,
			    'priority' => $priority
			);
    
}

function ah_add_boxes() {
global $admin_boxes_data;
    if ($admin_boxes_data==array()) return;
    foreach($admin_boxes_data as $boxname => $data)
	add_meta_box( $boxname, $data['description'], 'ah_add_box', $data['type'], $data['position'], $data['priority'], '' );
}


function ah_add_box($post,$box) {
global $admin_post_data;

    $boxname=$box['id'];
    $type = $post->post_type;
    
    $fields = $admin_post_data[$type][$boxname];
    wp_nonce_field( plugin_basename( __FILE__ ), $type.'_'.$boxname.'_noncename' );
    foreach($fields as $name => $field) {
	      if (@$field['hidden']) $type='hidden';
	      elseif (@$field['readonly']) $type='readonly';
	      else $type = $field['type'];
	      $value = get_post_meta($post->ID,$name,true);
	      if (!$value) $value=@$field['default'];
	      if ($field['type']=='date') {
			$value = date_create_from_format('Y-m-d',$value);
			$value = $value->format('d-m-Y');
	      }
	      admin_field($name, $type, $field['description'], $value, '', $field['howto'] );
    }
}

function ah_post_save($post_id) {
global $admin_post_data;

  $post = get_post($post_id);  
  $type = $post->post_type;
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )   return;
  if ( $type != $_POST['post_type'] ) return;
  if ( !current_user_can( 'edit_'.$type, $post_id )) return;  //@FIXME : dovrei usare il plurale per creare la capability

  
  foreach($admin_post_data[$type] as $boxname => $fields) {
    if ( wp_verify_nonce( $_POST[$type.'_'.$boxname.'_noncename'], plugin_basename( __FILE__ ) ) ) {
	foreach($fields as $name => $field) {
	    if ($field['type']=='date') {
		$value = date_create_from_format('d-m-Y',$_POST[$name]);
		$value = $value->format('Y-m-d');
	    } else $value = $_POST[$name];
	    admin_set_post_meta($post_id,$name,$value);
	}
    }
  }
   
}

function ah_post_get($post) {
global $admin_post_data;
    
    $type = $post->post_type;
    if (key_exists($type,$admin_post_data)) {
	foreach($admin_post_data[$type] as $boxname => $fields) {
	    foreach($fields as $name => $field) {
		    $value = get_post_meta($post->ID,$name,true);
		    if ($field['type']=='date') {
			$value = date_create_from_format('Y-m-d',$value);
			$value = $value->format('d-m-Y');
		    }
		    $post->$name =  $value;
	    }
	}
    }
}

function ah_hooks() {
    add_action("activated_plugin", "ah_first");
    add_action('admin_init','ah_init');
    add_filter('wp_footer','ah_footer');  
    add_filter('admin_footer','ah_footer');
    add_action('save_post','ah_post_save');
    add_filter("the_post", "ah_post_get", null, 2);
    add_filter("attachment_fields_to_edit", "ah_attachment_fields_to_edit", null, 2);
    add_filter("attachment_fields_to_save", "ah_attachment_fields_to_save", null, 2);
    add_filter('get_the_terms','ah_taxonomy_get_terms',1,3);
    add_action('admin_enqueue_scripts','ah_load_script');
}



function ah_attachment_fields_to_edit($form,$post) {
    return $form;
}

function ah_attachment_fields_to_save($post,$attachment) {
    return $post;
}

function ah_posttype_fields_to_edit($form,$post) {
    return $form;
}

function ah_posttype_fields_to_save($post,$attachment) {
    return $post;
}

function ah_load_script() {
    wp_register_script('admin_helper_js', plugins_url( 'admin_helper.js' , __FILE__ ));
    wp_enqueue_script('jquery');
    wp_enqueue_script('admin_helper_js');

    
}

function ah_init() {
    global $admin_debug_data;
	
	// sistema la gestione degli errori e del debug
	if (!function_exists('dbgx_trace_var')) {
	    set_error_handler("ah_error");
	}
	wp_register_style('admin_helper_css', plugins_url( 'admin_helper.css' , __FILE__ ));
	$admin_debug_data = array();
	
	wp_enqueue_style('admin_helper_css');

	
	// agginge eventuali box
	ah_add_boxes();
	
}

function ah_footer() {
global $admin_debug_data;
global $admin_post_data;
global $admin_boxes_data;
global $admin_taxonomy_data;


    // debug delle variabili globali
    admin_debug($admin_post_data,'admin_post_data');
    admin_debug($admin_boxes_data,'admin_boxes_data');
    admin_debug($admin_taxonomy_data,'admin_taxonomy_data');


    // stampa il debug, se non non ho altro
    if (count($admin_debug_data)==0) return '';
    $debug='';
    $debug.= '
	    <style>
			.debug_wrapper {
				width:80%;
				background:black;
				color : darkgray;
			}
			.debug_name {
				font-weight:bold;
				width : 60px;
			}
			.debug_time {
				font-style : italic;
			}
			.debug_log {
				color : blue;
			}
			.debug_message {
				color : yellow;
			}
			.debug_extend {
				background : #555555;
			}
			.debug_error {
				color : red;
			}
			.debug_warning {
				color : #FF7F00;
			}
			.debug_notice {
				color : #FFCC66;
			}
			
			.debug_strict {
				color : #FFCC66;
			}

			.debug_action {
				color : #9966FF;
			}
			
			.debug_filter {
				color : #6666CC;
			}
		
	    </style>
    ';
    $debug.= "<p class='debug_wrapper'>";
    foreach($admin_debug_data as $k => $line) {
			$data = htmlentities(print_r($line['var'],true));
			if (strlen($data)>100) $abstract=substr($data,0,100)." ...";
			else $abstract = $data;
			$data = nl2br("\n&nbsp;&nbsp;".$data);
			$debug.= "<span class='debug_{$line['type']}'><span class='debug_name'>{$k}:{$line['name']}</span>";
			$debug.= " @ ";
			$debug.= "<span class='debug_time'>".date('d-m-y H:i:s',$line['time'])."</span>";
			$debug.= "<span class='debug_view_refer'> # </span>";
			$debug.= "<span class='debug_refer'>".@$line['refer']." <br>&nbsp;&nbsp;&nbsp;</span>";
			$debug.= " &gt; ";
			$debug.= "<span class='debug_data'>$abstract</span>";
			$debug.= "<span class='debug_extend'>$data</span>";
			$debug.= "</span>";
			$debug.= '</span></br>';
	}
	$debug.= "</p>";
	$debug.= '
	<script>
	jQuery(document).ready(function() {
			jQuery(".debug_extend").hide();
			jQuery(".debug_refer").hide();
			
			jQuery(".debug_extend").click(function() {
				jQuery(this).toggle();
				jQuery(this).prev().toggle();
			});
			jQuery(".debug_view_refer").click(function() {
				jQuery(this).next().toggle();
			});
			jQuery(".debug_data").click(function() {
				jQuery(this).toggle();
				jQuery(this).next().toggle();
			});
	}); 
	</script>
	';
	// cancello così ogni cosa la vedo una sola volta
	
    return $debug;
}


function ah_error($errno, $errstr, $errfile, $errline) {
global $admin_debug_data;
	switch ($errno) {
    case E_USER_ERROR:
    case E_ERROR:
        	$type='error';
        break;
    case E_USER_WARNING:
    case E_WARNING:
        	$type='warning';
        break;
    case E_USER_NOTICE:
    case E_NOTICE:
        	$type='notice';
        break;
	case E_DEPRECATED:
        	$type='deprecated';
    break;    
    case E_STRICT:
    		$type='strict';
    break;
    default:
        	$type='unknow:'.$errno;
        break;
    }
	$admin_debug_data[] = array(
		'name' => 'error', "var" => $errstr, "type" => $type, "time" => microtime(true), "refer" => $errfile."@".$errline
	); 
	return true;
}


ah_hooks();