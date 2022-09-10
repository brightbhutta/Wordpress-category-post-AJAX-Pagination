<?php
function my_ajax_navigation() {
    $requested_page = intval($_POST['page']);
    $posts_per_page = intval($_POST['posts_per_page']) - 1;
    $posts = get_posts(array(
        'posts_per_page' => $posts_per_page,
        'offset' => $page * $posts_per_page
    ));
    foreach ($posts as $post) {
        setup_postdata( $post );
        // DISPLAY POST HERE
        // good thing to do would be to include your post template
    }
    exit;
}
add_action( 'wp_ajax_ajax_pagination', 'my_ajax_navigation' );
add_action( 'wp_ajax_nopriv_ajax_paginationr', 'my_ajax_navigation' );

?>

<?php
add_action( 'wp_ajax_nopriv_get_post_category', 'post_category' );
add_action( 'wp_ajax_get_post_category', 'post_category' );   
function post_category() {
    $post_type = $_POST['postType'];      
    $category = $_POST['category'];
    $search = $_POST['search'];
    $paged = ($_POST['paged'])? $_POST['paged'] : 1;
    if($post_type==="resource-center"):
        $taxonomy ="resource-center-taxonomy";
    else:
        $taxonomy ="category";
    endif;
    if($category):
        $args = array(
            'post_type'         => $post_type,
            'post_status'       => 'publish',
            'tax_query' => array(
                array(
                    'taxonomy' => $taxonomy,
                    'field'    => 'slug',
                    'terms'    => $category,
                ),
            ),
            'posts_per_page'    => 5,
            'order'             => 'ASC',
            's'                 => $search,
            'paged'             => $paged
        );
    else:
        $args = array(
            'post_type'         => $post_type,
            'post_status'       => 'publish',
            'posts_per_page'    => 5,
            'order'             => 'ASC',
            's'                 => $search,
            'paged'             => $paged
        );
    endif;

    $posts = new WP_Query($args);?>
    <?php if ( $posts->have_posts() ) :?>
        <?php while ($posts->have_posts()) : $posts->the_post(); ?>
    <?php echo $post->post_title; ?>
        <?php endwhile;?>
        <?php
            $nextpage = $paged+1;
            $prevouspage = $paged-1;
            $total = $posts->max_num_pages;
            $pagination_args = array(
            'base'               => '%_%',
            'format'             => '?paged=%#%',
            'total'              => $total,
            'current'            => $paged,
            'show_all'           => false,
            'end_size'           => 1,
            'mid_size'           => 2,
            'prev_next'          => true,
            'prev_text'       => __('<span class="prev-next" data-attr="'.$prevouspage.'">&laquo;</span>'),
            'next_text'       => __('<span class="prev-next" data-attr="'.$nextpage.'">&raquo;</span>'),
            'type'               => 'plain',
            'add_args'           => false,
            'add_fragment'       => '',
            'before_page_number' => '',
            'after_page_number'  => ''
        );
        $paginate_links = paginate_links($pagination_args);

        if ($paginate_links) {
            echo "<div id='pagination' class='pagination'>";
            echo $paginate_links;
            echo "</div>";
        }?>
        <?php wp_reset_query();  ?>
    <?php else:?>           
       <div class="no-post-cover">
            <div class="container">         
               <h1 class="has-no-post-list">Posts Not Found</h1>
            </div>
        </div>
   <?php endif;?>         
   <?php die(1);
}
?>

<script type="text/javascript">
    $('.pagination a').click(function(e) {
            e.preventDefault(); // don't trigger page reload
            if($(this).hasClass('active')) {
                return; // don't do anything if click on current page
            }
            $.post(
                '<?php echo admin_url('admin-ajax.php'); ?>', // get admin-ajax.php url
                {
                    action: 'ajax_pagination',
                    page: parseInt($(this).attr('data-page')), // get page number for "data-page" attribute
                    posts_per_page: <?php echo get_option('posts_per_page'); ?>
                },
                function(data) {
                    $('#content-posts').html(data); // replace posts with new one
                }
            });
        });

$('#post-category').change(function(){
            category = $(this).find('.selected').text();
            postType = $('#search-form-type').val();
            post_filter();
        });


    function post_filter(paged){
            $.ajax(
                {
                    url:ajaxUrl,
                    type:"POST",
                    data: {action:"get_post_category","category":category,'search':search, 'postType':postType, 'paged': paged},
                    success: function(response) {
                    $('#blog-post-cover').html(response);
                }
            });
        }

        $('#blog-wrapper').on('click','#pagination a',function(e){
            e.preventDefault();     
            if ($(this).hasClass('prev')||$(this).hasClass('next')) {
                paginateNum = $(this).find('.prev-next').data('attr');
                post_filter(paginateNum);
            }
            else{
                paginateNum = $(this).text();
                post_filter(paginateNum);
            }
            $("html, body").animate({ scrollTop: 0 }, "slow");
        });
        postType = $('#search-form-type').val();
        post_filter(1);
</script>