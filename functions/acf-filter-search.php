<?php

if (function_exists('pronamic_google_maps')) {
  add_action('admin_enqueue_scripts', 'pronamic_hack');
  function pronamic_hack(){
    wp_register_script('pronamic_hack_js', get_template_directory_uri() . '/js/pronamic-hack.js', false, '1.0.0');
    wp_enqueue_script('pronamic_hack_js');
  }

  function custom_map(){
    global $post;
    if (function_exists('pronamic_google_maps')) {
      pronamic_google_maps(array(
        'width' => '100%',
        'height' => '100%',
        'map_options' => array(
          'scrollwheel' => false
        ) ,
        'marker_options' => array(
          'icon' => get_stylesheet_directory_uri() . '/images/markerIcon.svg'
        )
      ));
    }
  }

  function add_map($query){
?>
    <script type="text/javascript">
      var map;
      var scroll = false;
      var styles = [
      {
        stylers: [
          { saturation: -100 }
        ]
      } ];
      var mapOptions = {
        zoom: 7,
        scrollwheel: scroll,
        mapTypeId: google.maps.MapTypeId.ROADMAP
      }

      // var styledMap = new google.maps.StyledMapType(styles, {name: "Styled Map"});

      var bounds;

      function initialize() {
        if(window.innerHeight < 600){
            scroll = false;
        }

        map = new google.maps.Map(document.getElementById("google_map_canvas"), mapOptions);
        bounds = new google.maps.LatLngBounds();

        // --- Add the markers ---

        <?php
        $icon = get_stylesheet_directory_uri() . '/images/markerIcon.svg';
        $m = 0;
        if ($query->have_posts()) {
          while ($query->have_posts()) {
            $query->the_post();
            $place = get_field('activity_place');
            $type = get_field('activity_type');
            $target = get_field('activity_target');
            $icons = '<ul class="masonry__icons-list"><li><svg class="ug-Svg-icon ug-Svg--'. $place .'"><use xlink:href="#ug-Svg--'. $place .'" /></svg></li><li><svg class="ug-Svg-icon ug-Svg--'. $type .'"><use xlink:href="#ug-Svg--'. $type .'" /></svg></li><li><svg class="ug-Svg-icon ug-Svg--'. $target .'"><use xlink:href="#ug-Svg--'. $target .'" /></svg></li></ul>';

            if (pronamic_get_google_maps_meta()->latitude && pronamic_get_google_maps_meta()->longitude):
              $m++;
              $showmap = true;
        ?>

        var myLatlng<?php echo $m ?> = new google.maps.LatLng(
          <?php echo pronamic_get_google_maps_meta()->latitude ?>, <?php echo pronamic_get_google_maps_meta()->longitude ?>
        );

        var marker<?php echo $m ?> = new google.maps.Marker({
          position: myLatlng<?php echo $m ?>,
          map: map,
          icon: '<?php echo $icon ?>'
        });

        var boxText<?php echo $m ?> = document.createElement("div");


        // --- INFOBOX CONTENT ---
        boxText<?php echo $m ?>.innerHTML = '<div class="uk-h4"><a href="<?php echo the_permalink(); ?>"><?php echo addslashes(the_title()) ?></a></div><?php echo $icons ?>';

        var myOptions<?php echo $m ?> = {
          content: boxText<?php echo $m ?>,
          disableAutoPan: false,
          maxWidth: 0,
          pixelOffset: new google.maps.Size(-112, 0),
          zIndex: null,
          boxStyle: {
              background: "",
              opacity: 1,
              width: "240px"
           },
          closeBoxMargin: "5px 5px 2px 2px",
          closeBoxURL: '  <?php echo get_stylesheet_directory_uri(); ?>/images/close.svg',
          infoBoxClearance: new google.maps.Size(1, 1),
          isHidden: false,
          pane: "floatPane",
          enableEventPropagation: false
        };

        var ib<?php echo $m ?> = new InfoBox(myOptions<?php echo $m ?>);
        var oldiBox;

        google.maps.event.addListener(marker<?php echo $m ?>, "click", function (e) {
          if( oldiBox != undefined ){
            oldiBox.close();
          }
          ib<?php echo $m ?>.open(map, this);
          map.setCenter(marker<?php echo $m ?>.getPosition());
          oldiBox = ib<?php echo $m ?>;
        });

        var boxText_<?php echo $m ?> = document.createElement("div");

        boxText_<?php echo $m ?>.style.cssText = "box-shadow: 3px 3px 0 0 rgba(0,0,0,0.12); background: #252525; padding: 11px;";

        boxText_<?php echo $m ?>.innerHTML = '<div class="uk-h4"><a href="<?php echo the_permalink(); ?>"><?php echo addslashes(the_title()) ?></a></div>';

        var myOptions<?php echo $m ?> = {
          content: boxText_<?php echo $m ?>,
          disableAutoPan: false,
          maxWidth: 0,
          pixelOffset: new google.maps.Size(-112, 0),
          zIndex: null,
          boxStyle: {
              background: "",
              opacity: 1,
              width: "240px"
          },
          closeBoxURL: ""
        };

        var iw<?php echo $m ?> = new InfoBox(myOptions<?php echo $m ?>);

        // google.maps.event.addListener(marker<?php echo $m ?>, 'mouseover', function() {
        //   iw<?php echo $m ?>.open(map, this);
        // });

        // google.maps.event.addListener(marker<?php echo $m ?>, 'mouseout', function() {
        //   iw<?php echo $m ?>.close();
        // });

        bounds.extend(myLatlng<?php echo $m ?>);

            <?php
            endif;
          } //endwhile query->have_posts
        } //endif query->have_posts
      ?>

        if (<?php echo $m ?> > 1) {
          map.fitBounds(bounds);
        }
        else if (<?php echo $m ?> == 1) {
          map.setCenter(bounds.getCenter());
          map.setZoom(12);
        }
      } // initialize()
    </script>
    <?php
  }
}

add_action('after_setup_theme', 'masonry_image_setup');

function masonry_image_setup(){
  add_image_size('masonry image', 480, 400, false);
}

function url_pic_masonry($id){
  global $post;
  $id = ($id) ? $id : $post->ID;
  if (has_post_thumbnail($id)) {
    $image_url = wp_get_attachment_image_src(get_post_thumbnail_id($id) , 'masonry image');
    return $image_url[0];
  }
}

function masonry_post_no_uk($map = false){
  global $post;

  $classes = '';
  $themes = '';
  $types = '';
  $data = '';
  $icon = '';
  if ($map) {
    $classes = ' mapped-masonry';
    $data = ' data-lat=' . pronamic_get_google_maps_meta()->latitude . ' data-lng=' . pronamic_get_google_maps_meta()->longitude;
  }

?>
  <div class="masonry__article <?php echo $classes; ?>"<?php echo $data; ?>>
    <div class="masonry__article__inner">
      <a href="<?php the_permalink(); ?>" class="masonry__article__image">
        <img src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-original="<?php echo url_pic_masonry($post->ID); ?>" alt="<?php the_title(); ?>" class="unveil" />
      </a>
      <h2 class="masonry__article__title">
        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
      </h2>
      <div class="masonry__article__content">
        <?php
          $place = get_field('activity_place');
          $type = get_field('activity_type');
          $target = get_field('activity_target');
        ?>
        <ul class="masonry__icons-list">
          <li><svg class="ug-Svg-icon ug-Svg--<?php echo $place; ?>"><use xlink:href="#ug-Svg--<?php echo $place; ?>" /></svg></li>
          <li><svg class="ug-Svg-icon ug-Svg--<?php echo $type; ?>"><use xlink:href="#ug-Svg--<?php echo $type; ?>" /></svg></li>
          <li><svg class="ug-Svg-icon ug-Svg--<?php echo $target; ?>"><use xlink:href="#ug-Svg--<?php echo $target; ?>" /></svg></li>
        </ul>
      </div>
    </div>
  </div>

  <?php
}

function navi_custom($query){
  if (get_query_var('paged')) {
    $paged = get_query_var('paged');
  }
  else
  if (get_query_var('page')) {
    $paged = get_query_var('page');
  }
  else {
    $paged = 1;
  }

  $howMany = $query->query_vars['posts_per_page'];
  $start = ($paged - 1) * $howMany;
  $count = $query->found_posts;
  $nombreDePages = $query->max_num_pages;
  $html = '<div class="infinite-pagenavi">';

  // $html = '<div class="wp-pagenavi">';
  // $html .=  '<ul class="">';

  $startPage = $paged - 4;
  if ($startPage < 1) {
    $startPage = 1;
  }

  $url = explode('page/', $_SERVER['REQUEST_URI']);
  $path = $url[0];
  $params = '';
  if (isset($_GET)) {
    foreach($_GET as $key => $value) {
      if ($key != 'page' && $key != 'paged') $params.= '&amp;' . $key . '=' . $value;
    }
  }

  if ($paged > 1) {
    $prev = $paged - 1;

    // $html .= '<li><a class="previouspostslink" href="'.get_permalink() . '?paged=' . $prev.$params.'">&lsaquo;</a></li>';

  }

  $endPage = $startPage + 5;
  if ($endPage > $nombreDePages + 1) $endPage = $nombreDePages + 1;
  for ($startPage; $startPage < $endPage; $startPage++) {

    // if($startPage == $paged) {
    //   $html .= '<li><span class="current">'.$startPage.'</span></li>';
    // }
    // else {
    //   $html .= '<li><a class="page larger" href="'.get_permalink(). '?paged=' . $startPage.$params.'">'.$startPage.'</a></li>';
    // }

  }

  $next = $paged + 1;
  if ($nombreDePages > $paged) {

    // $html .= '<li><a class="nextpostslink" href="'.get_permalink(). '?paged=' . $next.$params.'">&rsaquo;</a></li>';

    $restpages = $count - $howMany * $paged;
    if ($restpages > 0) {
      $html.= '<a class="nextpostslink" href="' . get_permalink() . '?paged=' . $next . $params . '"><svg class="ug-Svg-icon ug-Svg--more"><use xlink:href="#ug-Svg--more" /></svg>' . __('Display more results', 'site') . '<br />';
      $html.= '(<span class="rest">' . $restpages . '</span> ' . __('more', 'site') . ')</a>';
      $html.= '<span class="no-more-rest">' . __('No more results', 'site') . '</span>';
    }
  }

  // $html .= '</ul>';

  $html.= '</div>';
  return $html;
}

function get_acf_key_from_name($field_name){
  global $wpdb;
  $key = $wpdb->get_var("
      SELECT `post_name`
      FROM $wpdb->posts
      WHERE `post_name` LIKE 'field_%' AND `post_excerpt` LIKE '%$field_name%';
  ");
  return $key;
}

function get_field_choice_values($key = ''){
  $field = get_field_object($key);
  $html = '';
  $listItems = '';
  $btnLabel = $field['choices']['all'];
  $btnIcon = '<svg class="ug-Svg-icon ug-Svg--all-'.$field['name'].'"><use xlink:href="#ug-Svg--all-'.$field['name'].'" /></svg>';
  $params = '';
  if (isset($field)) {

    // Check each possible field value

    foreach($field['choices'] as $field_key => $field_value) {

      // add the value parameter in the filter link as GET

      $params = $field['name'] . '=' . $field_key;

      // check if there are some already selected filters

      if (isset($_GET) && count($_GET) > 0) {

        // for each filter that's already selected:

        foreach($_GET as $get_key => $get_value) {
          if ($get_key != $field['name']) {

            // if there are some already selected filters, we add their parameter value to the current link

            $params.= '&amp;' . $get_key . '=' . $get_value;
          }

          if ($get_value == $field_key) {
            $btnLabel = $field_value;
            $btnIcon = '<svg class="ug-Svg-icon ug-Svg--'. $field_key .'"><use xlink:href="#ug-Svg--'. $field_key .'" /></svg>';
            if($get_value=='all') {
              $btnIcon = '<svg class="ug-Svg-icon ug-Svg--'. $field_key .'-'.$field['name'].'"><use xlink:href="#ug-Svg--'. $field_key .'-'.$field['name'].'" /></svg>';
            }
          }
        }
      }
      // ... and we finally add all these parameters

      if($field_key=='all'){
        $listItemIcon = $field_key.'-'.$field['name'];
      }
      else{
        $listItemIcon = $field_key;
      }

      $listItems .= '<li><a href="' . get_permalink() . '?' . $params . '"><svg class="ug-Svg-icon ug-Svg--'. $listItemIcon .'"><use xlink:href="#ug-Svg--'. $listItemIcon .'" /></svg>' . $field_value . '</a></li>';
    }

    $html.= '<button class="uk-button">' . $btnIcon . $btnLabel . '</button>';
    $html.= '<div class="uk-dropdown"><ul>';
    $html.= $listItems;
    $html.= '</ul></div>';
  }

  return $html;
}

function get_field_bool_values($key = ''){
  $field = get_field_object($key);
  $html = '';
  $params = '';
  $switcher = '<svg xmlns="http://www.w3.org/2000/svg" width="47" height="23" class="switch" viewBox="0 0 47 23"><g fill="none" fill-rule="evenodd"><path fill="#149DCB" d="M34.973 0H11.7C5.274 0 .064 5.15.064 11.5S5.274 23 11.7 23h23.273C41.4 23 46.61 17.85 46.61 11.5S41.4 0 34.972 0z" class="switchcontainer"/><path fill="#000" d="M11.7 1.438h23.273c6.18 0 11.223 4.766 11.6 10.78.016-.238.036-.475.036-.718C46.61 5.15 41.4 0 34.97 0H11.7C5.274 0 .064 5.15.064 11.5c0 .243.022.48.036.72C.477 6.203 5.52 1.436 11.7 1.436z" opacity=".1"/><ellipse cx="33.896" cy="11.438" fill="#FFF" class="mainCircle" rx="7.65" ry="7.563"/><path fill="#000" d="M11.7 5.75c-3.213 0-5.818 2.575-5.818 5.75s2.605 5.75 5.818 5.75c3.213 0 5.82-2.575 5.82-5.75s-2.607-5.75-5.82-5.75zm0 8.625c-1.605 0-2.91-1.288-2.91-2.875s1.305-2.875 2.91-2.875c1.606 0 2.91 1.288 2.91 2.875s-1.304 2.875-2.91 2.875z" class="smallCircle" opacity=".1"/></g></svg>';

  if (isset($field)) {
    $paramsTrue = $field['name'] . '=1';
    if (isset($_GET) && count($_GET) > 0) {

      // for each filter that's already selected:

      foreach($_GET as $get_key => $get_value) {
        if ($get_key != $field['name']) {

          // if there are some already selected filters, we add their parameter value to the current link

          $params.= '&amp;' . $get_key . '=' . $get_value;
        }
      }
    }
  }

  $html.= '<div class="filter filter--bool">';
  if (isset($_GET[$field['name']])) {
    $html.= '<a href="' . get_permalink() . '?' . $params . '" class="uk-button uk-button--bool uk-button--active"></span><span class="text"><svg class="ug-Svg-icon ug-Svg--'. $field['name'] .'"><use xlink:href="#ug-Svg--'. $field['name'] .'" /></svg>' . $field['label'] . $switcher . '</span><span class="sr-only">: ' . __('No', 'site') . '</span></a>';
  }
  else {
    $html.= '<a href="' . get_permalink() . '?' . $paramsTrue . $params . '" class="uk-button uk-button--bool"><span class="text"><svg class="ug-Svg-icon ug-Svg--'. $field['name'] .'"><use xlink:href="#ug-Svg--'. $field['name'] .'" /></svg>' . $field['label'] . $switcher . '</span><span class="sr-only">: ' . __('Yes', 'site') . '</span></a>';
  }

  $html.= '</div>';
  return $html;
}

function get_link_range($field_name1, $field_name2){
  $field_value1 = get_range_value($field_name1);
  $field_value2 = get_range_value($field_name2, true);
  $params = $field_name1 . '=' . $field_value1 . '&amp;' . $field_name2 . '=' . $field_value2;
  $html = '';
  if (isset($_GET) && count($_GET) > 0) {
    foreach($_GET as $get_key => $get_value) {
      if ($get_key != $field_name1 && $get_key != $field_name2) {
        $params.= '&amp;' . $get_key . '=' . $get_value;
      }
    }
  }

  $html.= get_permalink() . '?' . $params;
  return $html;
}

function get_range_value($filterName, $max = false){
  global $wpdb;
  $values = array();
  $valuesObj = $wpdb->get_results("
      SELECT `meta_value`
      FROM $wpdb->postmeta
      WHERE `meta_key` = '$filterName';
  ");
  if (count($valuesObj)) {
    foreach($valuesObj as $obj) {
      array_push($values, $obj->meta_value);
    }

    if ($max) {
      $val = max($values);
    }
    else {
      $val = min($values);
    }

    return $val;
  }
}

function get_all_entered_values($filterName){
  global $wpdb;
  $values = array();
  $valuesObj = $wpdb->get_results("
    SELECT DISTINCT `meta_value`  FROM $wpdb->postmeta
    RIGHT JOIN $wpdb->posts
    ON $wpdb->posts.ID=$wpdb->postmeta.post_id
    WHERE meta_key='$filterName'
    AND $wpdb->posts.post_status='publish' ORDER BY `meta_value` ASC
  ");
  return $valuesObj;
}

function get_curr_range_value($filterName, $max = false){
  if (isset($_GET[$filterName])) {
    $curr = $_GET[$filterName];
  }
  else {
    $curr = get_range_value($filterName, $max);
  }

  return $curr;
}

function get_filter_choices($filterName){

  // Since ACF gives the possibility to retrieve a field info through its key, we use get_acf_key_from_name to retrieve the key

  $key = get_acf_key_from_name($filterName);

  // Once we have the key, we get each value from the field

  $html = '<div class="filter filter--choices uk-button-dropdown filter--choices--' . $filterName . '" data-uk-dropdown="{mode:\'click\'}">';
  $html.= get_field_choice_values($key);
  $html.= '</div>';

  // ... and we return it

  return $html;
}

function get_filter_slider($filterName, $filterLabel, $filterDataHour){
  $filterMin = $filterName . '_min';
  $filterMax = $filterName . '_max';
  $html = '<div class="filter filter--range-slider uk-clearfix uk-button-slideDown">';
    $html.= '<button class="uk-button"><svg class="ug-Svg-icon ug-Svg--'. $filterName .'"><use xlink:href="#ug-Svg--'. $filterName .'" /></svg>' . $filterLabel . '</button>';
    $html.= '<div class="uk-slideDown">';
      $html.= '<div class="nstSlider ' . $filterName . '" data-replace="' . $filterName . '" data-range_min="0" data-range_max="' . get_range_value($filterMax, true) . '" data-cur_min="' . get_curr_range_value($filterMin, false) . '"  data-cur_max="' . get_curr_range_value($filterMax, true) . '" data-link="' . get_link_range($filterMin, $filterName . '_max') . '" data-hour="' . $filterDataHour . '">';

        $html.= '<div class="highlightPanel"></div>';

        // 2.4. (optional) this is the bar that fills the area between the left and the right grip -->
        $html.= '<div class="bar"></div>';
        // 2.5  the left grip
        $html.= '<div class="leftGrip"></div>';
        // 2.6  (optional) the right grip. Just omit if you don't need one
        $html.= '<div class="rightGrip"></div>';
      $html.= '</div>';

      // These two are actually exernal to the plugin, but you are likely to need them... the plugin does the math, but it's up to you to update the content of these two elements.
      $html.= '<div class="leftLabel"></div>';
      $html.= '<div class="rightLabel"></div>';
    $html.= '</div>';
  $html.= '</div>';
  return $html;
}

function get_filter_bool($filterName){

  // Since ACF gives the possibility to retrieve a field info through its key, we use get_acf_key_from_name to retrieve the key

  $key = get_acf_key_from_name($filterName);

  // Once we have the key, we get bool values from the field

  $filter = get_field_bool_values($key);
  return $filter;
}

function get_filterText($filterName){

  // Since ACF gives the possibility to retrieve a field info through its key, we use get_acf_key_from_name to retrieve the key

  $key = get_acf_key_from_name($filterName);
  $field = get_field_object($key);
  $btnLabel = $field['label'];
  $allParams = '';
  $filter = '';
  $lis = '';
  $params = '';
  if (isset($field)) {
    $filter = '<div class="filter filter--choices uk-button-dropdown" data-uk-dropdown="{mode:\'click\'}">';

    // Once we have the key, we get bool values from the field

    $textValues = get_all_entered_values($filterName);
    foreach($textValues as $value) {
      $params = $field['name'] . '=' . urlencode($value->meta_value);
      if (isset($_GET) && count($_GET) > 0) {

        // for each filter that's already selected:

        foreach($_GET as $get_key => $get_value) {

          // if there are some already selected filters, we add their parameter value to the current link
          // AVOID CONFLICTS BETWEEN CP AND TOWN

          if ($get_key != $field['name']) {
            if (($get_key == 'cp' && $field['name'] == 'town')) {
            }
            elseif ($get_key == 'town' && $field['name'] == 'cp') {
            }
            else {
              $params.= '&amp;' . $get_key . '=' . $get_value;
            }
          }

          if (urldecode($get_value) == $value->meta_value) {
            $btnLabel = $value->meta_value;
          }
        }
      }

      // ... and we finally add all these parameters

      $lis.= '<li><a href="' . get_permalink() . '?' . $params . '" class="list-js-' . $field['name'] . '">' . $value->meta_value . '</a></li>';
    }

    if (isset($_GET) && count($_GET) > 0) {
      foreach($_GET as $get_key => $get_value) {
        if ($get_key != $field['name'] && $get_key != 'cp' && $get_key != 'town') {
          $allParams.= '&amp;' . $get_key . '=' . $get_value;
        }
      }
    }

    $filter.= '<button class="uk-button"><svg class="ug-Svg-icon ug-Svg--'. $field['name'] .'"><use xlink:href="#ug-Svg--'. $field['name'] .'" /></svg>' . $btnLabel . '</button>';
    $filter.= '<div class="uk-dropdown" id="list-js-' . $field['name'] . '-main">';
    $filter.= '<form class="uk-form"><input type="search" placeholder="Search" class="search"></form>';
    $filter.= '<ul class="list">';
    $filter.= '<li><a href="' . get_permalink() . '?' . $allParams . '">' . __('All', 'site') . '</a></li>';
    $filter.= $lis;
    $filter.= '</ul></div></div>';
  }

  return $filter;
}

function get_filters($filters){
  $html = '';
  foreach($filters as $filter) {
    switch ($filter['type']) {
    case 'rangeSlider':
      $html.= get_filter_slider($filter['name'], $filter['label'], $filter['data-hour']);
      break;

    case 'text':
      $html.= get_filterText($filter['name']);
      break;

    case 'bool':
      $html.= get_filter_bool($filter['name']);
      break;

    case 'choice':
      $html.= get_filter_choices($filter['name']);
      break;

    default:
      $html.= get_filter($filter['name']);
    }
  }

  return $html;
}

function wp_posts_where($where, &$wp_query){
  global $wpdb;
  if ($wp_query->get('filter_search') && $wp_query->get('filter_search') != '') {
    $where.= ' AND (' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql($wpdb->esc_like($wp_query->get('filter_search'))) . '%\'';
    $where.= 'OR ' . $wpdb->posts . '.post_content LIKE \'%' . esc_sql($wpdb->esc_like($wp_query->get('filter_search'))) . '%\')';
  }

  return $where;
}
