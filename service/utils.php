<?php
/**
 * 输出表情
 * @param string $str
 * @return array|string|string[]|null {}转换后的字符串
 */
function xm_output_smiley(string $str = "") {
    return preg_replace_callback("/(\[(?!img)\w+(\-[\w\x{4e00}-\x{9fa5}]+)?\])/u", function ($matchs) {
        global $wpsmiliestrans;
        $smilies_dir = get_option("xm_vue_options")["domain"] . '/images/smilies/';
        return '<img src="' . $smilies_dir . $wpsmiliestrans[$matchs[1]] . '" width="20" style="vertical-align:bottom;box-shadow:none;">';
    }, $str);
}

/**
 * 需要替换的地址
 * @param [type] $url 需要替换的地址，示例：https://www.xuanmo.xin/wp-json
 * @return array|string|string[]|null 返回替换后的地址，示例：/wp-json
 */
function replace_domain($url) {
    return preg_replace("/https?:\/\/(\w+\.)+\w+(:\d+)?/", "", $url);
}

/**
 * 生成用户评论头像
 * @param bool $isText
 * @param string $email
 * @return string|void
 */
function xm_generate_user_avatar(bool $isText, string $email) {
    global $avatar_colors;
    if ($isText) {
        preg_match("/\d/", md5($email), $matches);
        return $avatar_colors[$matches[0]];
    }
}

/**
 * 转换评论中的图片
 * @param $comment
 * @return array|string|string[]|null
 */
function xm_transform_comment_img($comment) {
    return preg_replace_callback("/\[img\]\s?((https?:\/\/(\w+\.)+\w+(:\d+)?)?(\/[\w\-]+)+\.\w+)\[\/img\]/", function ($matchs) {
        return "<img src='$matchs[1]' class='comment-list-item--upload-img'  alt='' />";
    }, $comment);
}

/** 格式化返回的数据
 * @param $obj
 * @return array
 */
function xm_format_comment_item($obj): array {
    global $xm_theme_options;
    global $avatar_domain;
    return array(
        'id'           => $obj->comment_ID,
        'parentId'     => $obj->comment_parent,
        'postId'       => $obj->comment_post_ID,
        'authorName'   => $obj->comment_author,
        'authorSite'   => $obj->comment_author_url,
        'createTime'   => $obj->comment_date,
        'isApproved'   => (bool)$obj->comment_approved,
        'content'      => xm_output_smiley(xm_transform_comment_img($obj->comment_content)),
        'ua'           => $obj->comment_agent,
        'opinion'      => get_metadata('comment', $obj->comment_ID, 'opinion', true),
        'isTextAvatar' => (boolean)$xm_theme_options['text_pic'],
        'avatar'       => "https://$avatar_domain/avatar/" . md5(strtolower(trim($obj->comment_author_email))) . "?s=100",
        'avatarColor'  => xm_generate_user_avatar((boolean)$xm_theme_options['text_pic'], $obj->comment_author_email),
        'authorLevel'  => get_author_level($obj->comment_author_email)
    );
}

/**
 * 统计评论子级条数
 * @param string $comment_id 当前评论 ID
 * @return int
 */
function xm_get_comment_count(string $comment_id): int {
    $result = array();
    $comments = get_comments(array(
        'parent' => $comment_id,
        'status' => 'approve'
    ));
    $fn = function (&$comments) use (&$fn, $result) {
        foreach ($comments as $value) {
            $result[] = $value;
            $children = get_comments(array(
                'parent' => $value->comment_ID,
                'status' => 'approve'
            ));
            if (!empty($children)) {
                $result = array_merge($result, $fn($children));
            }
        }
        return $result;
    };
    return count($fn($comments));
}

/**
 * 递归查询评论列表
 * @param array $list 需要递归查询的列表
 * @param int $level 需要查询子级的层级数
 * @param string | null $parent_index 父级索引值
 * @param array $parent 父级信息
 * @return array
 */
function recursion_query_common_list(array $list, int $level = 2, string $parent_index = null, array $parent = array()): array {
    foreach ($list as $key => $value) {
        $uni_key = "$parent_index" . 0;
        $format_value = xm_format_comment_item($value);
        $children = get_comments(array(
            'parent' => $value->comment_ID,
            'order'  => 'ASC',
            'status' => 'approve',
            // 默认只查询2条子级数据
            'number' => $level === 0 ? '' : 2
        ));
        $list[$key] = array_merge($format_value, array(
            '_level'      => $uni_key,
            'parent'      => array(
                'content'     => (string)$parent['content'],
                'authorName'  => (string)$parent['authorName'],
                'authorSite'  => (string)$parent['comment_author_url'],
                'authorLevel' => get_author_level($parent['comment_author_email'])
            )
        ));

        if (strlen($uni_key) === 1) {
            $childrenCount = xm_get_comment_count($value->comment_ID);
            $list[$key]['hasChildren'] = $childrenCount > 2;
            $list[$key]['childrenCount'] = $childrenCount;
        }

        if (empty($children)) {
            $list[$key]['children'] = array();
        } else {
            if ($level === 0) {
                $list[$key]['children'] = recursion_query_common_list($children, $level, $uni_key, $format_value);
            } else {
                if (strlen($uni_key) > $level - 1) {
                    $list[$key]['children'] = array();
                } else {
                    $list[$key]['children'] = recursion_query_common_list($children, $level, $uni_key, $format_value);
                }
            }
        }
    }
    return $list;
}
