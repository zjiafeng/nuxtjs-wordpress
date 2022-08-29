<?php
require_once(TEMPLATEPATH . '/include/v2/Response.php');

/**
 * 获取全站的文章、分类、标签、页面
 * @return array
 */
function get_all_list() {
    global $wpdb;
    $response = new Response();

    // 查出所有的文章
    $article = $wpdb->get_results("SELECT id, post_title AS title, post_date AS createTime FROM $wpdb->posts WHERE (post_status = 'publish' OR post_status = 'private') AND post_type='post' ORDER BY post_date DESC");

    // 查出所有的页面
    $pages = $wpdb->get_results("SELECT id, post_title AS title, post_date AS createTime FROM $wpdb->posts WHERE (post_status = 'publish' OR post_status = 'private') AND post_type='page' ORDER BY post_date DESC");

    // 所有的标签
    $tags = get_tags(array("orderby" => "count", "order" => "DESC"));
    foreach ($tags as $key => $value) {
        $tags[$key] = array(
            'id'    => $value->term_id,
            'title' => $value->name,
            'count' => $value->count
        );
    }

    // 所有的分类
    $categorys = wp_get_nav_menu_items("Home");
    foreach ($categorys as $key => $value) {
        $categorys[$key] = array(
            'id'    => $value->object_id,
            'title' => $value->title
        );
    }

    $response->setResponse(array(
        'articles' => $article,
        'pages'    => $pages,
        'tags'     => $tags,
        'category' => $categorys
    ));
    return $response->getResponse();
}

add_action('rest_api_init', function () {
    register_rest_route('/xm/v2', '/site/list/all', array(
        'methods'             => 'get',
        'permission_callback' => '__return_true',
        'callback'            => 'get_all_list'
    ));
});

/**
 * 查询评论列表
 * @return array
 */
function get_comment_list() {
    $response = new Response();

    /** 格式化返回的数据
     * @param $obj
     * @return array
     */
    function format($obj) {
        return array(
            'id'         => $obj->comment_ID,
            'postId'     => $obj->comment_post_ID,
            'ahutor'     => $obj->comment_author,
            'authorUrl'  => $obj->comment_author_url,
            'createTime' => $obj->comment_date,
            'content'    => $obj->comment_content,
            'ua'         => $obj->comment_agent,
            'opinion'    => get_metadata('comment', $obj->comment_ID, 'opinion', true)
        );
    }

    // 查询子级数据
    $comment_list = get_comments(array(
        'post_id'  => $_GET['postId'],
        'parent'   => 0,
        'number'   => 10,
        'paged'    => 2,
        'meta_key' => 'opinion'
    ));

    $result = array();

    foreach ($comment_list as $key => $list) {
        $children_response = get_comments(array(
            'post_id' => $_GET['postId'],
            'parent'  => $list->comment_ID
        ));
        $children = array();

        foreach ($children_response as $child_key => $child) {
            $children[$child_key] = format($child);
        }

        $result[$key] = format($list);
        $result[$key]['children'] = $children;
    }

    $response->setResponse($result);

    return $response->getResponse();
}

add_action('rest_api_init', function () {
    register_rest_route('/xm/v2', '/comment/list', array(
        'methods'             => 'get',
        'permission_callback' => '__return_true',
        'callback'            => 'get_comment_list'
    ));
});
