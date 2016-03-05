<?php
/**
 * Template Name: Activities list with filters
 */
?>


<?php Starkers_Utilities::get_template_parts( array( 'parts/shared/html-header', 'parts/shared/header' ) ); ?>

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
<?php
  $pageFeaturedImage = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' );
?>
<div class="hero hero--default">
  <div class="hero--alt__img" style="background-image: url(<?php echo $pageFeaturedImage[0]; ?>);"></div>
  <div class="uk-container uk-container-center">
    <div class="uk-grid">
      <div class="uk-width-medium-6-12 uk-push-1-12 hero-header">
      </div>
    </div>
  </div>
</div>
<?php endwhile; ?>

<div class="uk-container uk-container-center">
  <div class="main-container main-container--activities main-container-mt-neg">
    <main class="masonry-container">
      <div class="pos-r">

        <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

        <div class="hero-header">
          <h1 class="hero__title"><?php the_title(); ?></h1>
          <?php
          if(get_field('page_subtitle'))
          {
            echo '<p class="hero__subtitle">' . get_field('page_subtitle') . '</p>';
          }
          ?>
        </div>

        <?php endwhile; ?>

        <div class="filters filters-aligned"></div>

        <div class="masonry">
          <div class="filters-content">
            <?php
              $filter1 = array(
                'name'     =>'activity_place',
                'type'     =>'choice'
              );
              $filter2 = array(
                'name'     =>'activity_type',
                'type'     =>'choice'
              );
              $filter3 = array(
                'name'     =>'target',
                'type'     =>'choice'
              );

              // Range sliders: params are mandatory
              // Indicate the label and the data type (hour or not) so it displays it in days/hours/min
              $filter4 = array(
                'name'      =>'people_num',
                'type'      =>'rangeSlider',
                'label'     => __('Number of players', 'site'),
                'data-hour' =>'false'
              );
              $filter5 = array(
                'name'      =>'hour_num',
                'type'      =>'rangeSlider',
                'label'     => __('Duration', 'site'),
                'data-hour' =>'true'
              );
              $filter6 = array(
                'name'      =>'activity_follow_up',
                'type'      =>'bool'
              );
              $filter7 = array(
                'name'      =>'personalisation',
                'type'      =>'bool'
              );
              $filter8 = array(
                'name'      =>'in_movement',
                'type'      =>'bool'
              );

              $filters=array($filter1, $filter2, $filter3, $filter4, $filter5, $filter6, $filter7, $filter8);
              //DISPLAY FILTERS
              echo get_filters($filters);
            ?>
            <div class="filter-search">
              <form action="" method="get">
                <input type="search" name="filter_search" id="filter-search__input" placeholder="<?php _e('Search', 'site');?>" value="<?php if(isset($_GET['filter_search'])) echo $_GET['filter_search'];?>">
                <!-- input type="hidden" name="post_type" value="activite" -->
                <?php
                  if(isset($_GET) && count($_GET) > 0) {
                    foreach ($_GET as $getKey => $getValue) {
                      if( $getKey != 'page' && $getKey != 'paged' && $getKey != 'filter_search' && $getValue != '' ) {
                        echo '<input type="hidden" name="'. $getKey .'" value="' . $getValue . '">';
                      }
                    }
                  }
                ?>
                <button class="search-icon"><svg class="ug-Svg-icon ug-Svg--search"><use xlink:href="#ug-Svg--search" /></svg></button>
              </form>
            </div>
            <?php if(isset($_GET) && count($_GET) > 0) { ?>
            <div class="filter filter-reset">
              <a href="<?php echo get_permalink();?>?" class="uk-button">Reset <svg class="ug-Svg-icon ug-Svg--closemenu"><use xlink:href="#ug-Svg--closemenu" /></svg></a>
            </div>
            <?php } ?>
          </div>

          <?php

            if ( get_query_var('paged') ) {
                $paged = get_query_var('paged');
            } else if ( get_query_var('page') ) {
                $paged = get_query_var('page');
            } else {
                $paged = 1;
            }

            // doing the meta_query based on $_GETs

            $args=array();
            $args['post_type']= 'activite';
            $meta_query = array();

            if(isset($_GET) && count($_GET) > 0) {
              foreach ($_GET as $key => $value) {
                if( $key != 'page' && $key != 'paged' && $value != 'all' && $key != 'filter_search' && $key != 'post_type' && $value != '' ) {
                  $lastCharsKey = substr($key, -4);
                  switch($lastCharsKey) {
                    // RANGES: if either min value or max value of an activity is between the min and max selection, we display it :)
                    // We detect the range stuff with their end ('_min' and '_max'). Not very clean but that's the only way I found
                    case '_min':
                      $firstCharsKey = substr($key, 0, -4);
                      $mq1 = array(
                        'key'     => $key,
                        'value'   => array($value-1, $_GET[$firstCharsKey.'_max']+1), 'compare' => 'BETWEEN','type'   => 'NUMERIC'
                      );
                      $mq2 = array(
                        'key'     => $firstCharsKey.'_max',
                        'value'   => array($value-1, $_GET[$firstCharsKey.'_max']+1),
                        'compare' => 'BETWEEN',
                        'type' => 'NUMERIC'
                      );
                      $mq = array(
                        'relation' => 'OR',
                        $mq1,
                        $mq2
                        );
                      break;
                    case '_max':
                        $mq=null;
                      break;

                    // other values (select choices)
                    default:
                      $mq = array(
                        'key'      => $key,
                        'value'    => $value,
                        'compare'  => 'LIKE'
                      );
                  }
                  if($mq != null)
                    array_push($meta_query, $mq);
                }
                if($key == 'filter_search' ) {
                  //echo $key . ' - ' . $value;
                  if($value != '')
                    $args['filter_search']=$value;
                }
              }
            }
            if(count($meta_query) > 0) {
              $args['meta_query'] = $meta_query;
            }
            $args['posts_per_page'] = 6;
            $args['paged'] = $paged;
            add_filter( 'posts_where', 'wp_posts_where', 10, 2 );

            $query = new WP_Query( $args );
            remove_filter( 'posts_where', 'wp_posts_where', 10, 2 );

            ?>


            <?php if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post(); ?>

            <?php masonry_post_no_uk(); ?>

            <?php endwhile; ?>
            <?php else: ?>
                  <h2 class="no-result"><?php _e('No result. Try other filters or click on the "reset" button', 'site');?>.</h2>
            <?php endif; ?>

            <?php wp_reset_query();?>
            <?php wp_reset_postdata(); ?>
            <?php
              // Custom navigation because wp-pagenavi is buggy
              echo navi_custom( $query );
            ?>


        </div>
        <div class="infinite-pagenavi-container"></div>

      </div>
    </main>
  </div>
</div>

<?php Starkers_Utilities::get_template_parts( array( 'parts/shared/footer','parts/shared/html-footer' ) ); ?>
