<?php
/**
* 
* @version 1.0
*
* @author Dilip7597 (Dilip Gupta - https://www.dsourc.com/)
* @link https://github.com/Dilip7597/bt4wp_cpt_breadcrumb/blob/master/bootstrap-breadcrumb.php
* 
* I added the support for Custom post type
*/


/**
 * Retrieve category parents.
 * @param  int $id Category ID.
 * @param  array $visited Optional. Already linked to categories to prevent duplicates.
 * @return string|WP_Error A list of category parents on success, WP_Error on failure.
 */
function custom_get_category_parents( $id, $visited = array() ) {

    $chain = '';
    $parent = get_term( $id, 'category' );

    if ( is_wp_error( $parent ) )
        return $parent;

    $name = $parent->name;

    if ( $parent->parent && ( $parent->parent != $parent->term_id ) && !in_array( $parent->parent, $visited ) ) {
        $visited[] = $parent->parent;
        $chain .= custom_get_category_parents( $parent->parent, $visited );
    }

    $chain .= '<li itemscope itemtype="http://data-vocabulary.org/Breadcrumb" class="breadcrumb-item"><a href="' . esc_url( get_category_link( $parent->term_id ) ) . '" itemprop="url"><span itemprop="title">' . $name . '</span></a>' . '</li>';

    return $chain;
}

/**
 * Breadcrumb Function]
 * @return [string] [breadcrumb output]
 */
function bootstrap_breadcrumb() {
    global $post;
    $schema_link = 'http://data-vocabulary.org/Breadcrumb';
    $html = '<nav aria-label="breadcrumb" class="small text-muted mb-5"><ol class="breadcrumb">';

    $post_type = get_post_type($post);

    if ( (is_front_page()) || (is_home()) ) {
        $html .= '<li itemscope itemtype="' . $schema_link . '" class="breadcrumb-item active" aria-current="page"><span itemprop="title">' . __('Home', 'wordflex') . '</span></li>';
    }

    else {
        $html .= '<li itemscope itemtype="' . $schema_link . '" class="breadcrumb-item"><a href="' . esc_url(home_url('/')) . '" itemprop="url"><span itemprop="title">' . __('Home', 'wordflex') . '</span></a></li>';

        if ( is_attachment() ) {
            $parent = get_post($post->post_parent);
            $categories = get_the_category($parent->ID);

            if ( $categories[0] ) {
                $html .= custom_get_category_parents($categories[0]);
            }

            $html .= '<li itemscope itemtype="' . $schema_link . '" class="breadcrumb-item"><a href="' . esc_url( get_permalink( $parent ) ) . '" itemprop="url"><span itemprop="title">' . $parent->post_title . '</span></a></li>';
            $html .= '<li itemscope itemtype="' . $schema_link . '" class="breadcrumb-item active" aria-current="page"><span itemprop="title">' . get_the_title() . '</span></li>';
        }
        if (get_page_by_path('blog')) {
            if (!is_page()) {
                $html .= '<li itemscope itemtype="' . $schema_link . '" class="breadcrumb-item"><a href="' . get_permalink(get_page_by_path('blog')) . '" itemprop="url"><span itemprop="title">' . __('Blog', 'wordflex') . '</span></a></li>';
            }
        }

        if ( is_category() ) {
            $category = get_category( get_query_var( 'cat' ) );

            if ( $category->parent != 0 ) {
                $html .= custom_get_category_parents( $category->parent );
            }

            $html .= '<li itemscope itemtype="' . $schema_link . '" class="breadcrumb-item active" aria-current="page"><span itemprop="title">' . single_cat_title( '', false ) . '</span></li>';
        }

        elseif ( is_page() && !is_front_page() ) {
            $parent_id = $post->post_parent;
            $parent_pages = array();

            while ( $parent_id ) {
                $page = get_page($parent_id);
                $parent_pages[] = $page;
                $parent_id = $page->post_parent;
            }

            $parent_pages = array_reverse( $parent_pages );

            if ( !empty( $parent_pages ) ) {
                foreach ( $parent_pages as $parent ) {
                    $html .= '<li itemscope itemtype="' . $schema_link . '" class="breadcrumb-item"><a href="' . esc_url( get_permalink( $parent->ID ) ) . '" itemprop="url"><span itemprop="title">' . get_the_title( $parent->ID ) . '</span></a></li>';
                }
            }

            $html .= '<li itemscope itemtype="' . $schema_link . '" class="breadcrumb-item active" aria-current="page"><span itemprop="title">' . get_the_title() . '</span></li>';
        }

        elseif ( is_archive() ) {
            $html .= '<li itemscope itemtype="' . $schema_link . '" class="breadcrumb-item active" aria-current="page"><span itemprop="title">' . post_type_archive_title( '', false ) . '</span></li>';
        }

        elseif ( is_singular( $post_type ) ) {
            $categories = get_the_category();

            if ( $categories[0] ) {
                $html .= custom_get_category_parents($categories[0]);
            }

            if (get_post_type() == $post_type) {
                $cpt = get_post_type_object( $post_type );

                $html .= '<li itemscope itemtype="' . $schema_link . '" class="breadcrumb-item" aria-current="page"><a href="' . esc_url( get_post_type_archive_link($post_type) ) . '" itemprop="url"><span itemprop="title">' . $cpt->labels->name . '</span></a></li>';
            }

            $html .= '<li itemscope itemtype="' . $schema_link . '" class="breadcrumb-item active" aria-current="page"><span itemprop="title">' . get_the_title() . '</span></li>';
        }

        elseif ( is_tag() ) {
            $html .= '<li itemscope itemtype="' . $schema_link . '" class="breadcrumb-item active" aria-current="page"><span itemprop="title">' . single_tag_title( '', false ) . '</span></li>';
        }


        elseif ( is_day() ) {
            $html .= '<li itemscope itemtype="' . $schema_link . '" class="breadcrumb-item"><a href="' . esc_url( get_year_link( get_the_time( 'Y' ) ) ) . '" itemprop="url"><span itemprop="title">' . get_the_time( 'Y' ) . '</span></a></li>';
            $html .= '<li itemscope itemtype="' . $schema_link . '" class="breadcrumb-item"><a href="' . esc_url( get_month_link( get_the_time( 'Y' ), get_the_time( 'm' ) ) ) . '" itemprop="url"><span itemprop="title">' . get_the_time( 'm' ) . '</span></a></li>';
            $html .= '<li itemscope itemtype="' . $schema_link . '" class="breadcrumb-item active" aria-current="page"><span itemprop="title">' . get_the_time('d') . '</span></li>';
        }

        elseif ( is_month() ) {
            $html .= '<li itemscope itemtype="' . $schema_link . '" class="breadcrumb-item"><a href="' . esc_url( get_year_link( get_the_time( 'Y' ) ) ) . '" itemprop="url">' . get_the_time( 'Y' ) . '</a></li>';
            $html .= '<li itemscope itemtype="' . $schema_link . '" class="breadcrumb-item active" aria-current="page"><span itemprop="title">' . get_the_time( 'F' ) . '</span></li>';
        }

        elseif ( is_year() ) {
            $html .= '<li itemscope itemtype="' . $schema_link . '" class="breadcrumb-item active" aria-current="page"><span itemprop="title">' . get_the_time( 'Y' ) . '</span></li>';
        }

        elseif ( is_author() ) {
            $html .= '<li itemscope itemtype="' . $schema_link . '" class="breadcrumb-item active" aria-current="page"><span itemprop="title">' . get_the_author() . '</span></li>';
        }

        elseif ( is_search() ) {

        }

        elseif ( is_404() ) {

        }
    }

    $html .= '</ol></nav>';

    echo $html;
}
