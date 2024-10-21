<?php

namespace HelpieFaq\Features\Notices;

if (!class_exists('\HelpieFaq\Features\Notices\View')) {
    class View
    {
        public function get_content($args)
        {

            $group_id = isset($args['group_id']) && !empty($args['group_id']) && intval($args['group_id']) ? $args['group_id'] : '';
            // $group_link = admin_url("edit-tags.php?taxonomy=helpie_faq_group&post_type=helpie_faq");
            global $post;
            $post_id = isset($post) ? $post->ID : '';
            if (empty($group_id) || empty($post_id)) {
                return;
            }
            $model = new \HelpieFaq\Features\Notices\Model();
            $group = $model->get_group($group_id);
            if (empty($group)) {
                return;
            }

            $group_link = admin_url("term.php?taxonomy=helpie_faq_group&tag_ID={$group_id}&post_type=helpie_faq");

            $edit_post_link = admin_url("post.php?post={$post_id}&action=edit");
            $notice_shortcode_content = esc_html('&#91;helpie_notices group_id=' . $group_id . '&#93;');

            $html = '';
            $html = '<div class="helpie-notices helpie-notices--info">';
            $html .= '<p class="helpie-notices__text"><b>' . esc_html__("Notice to Admin", "helpie-faq") . '</b> (' . esc_html__("only admin can see this", "helpie-faq") . ')</p>';
            $html .= '<p class="helpie-notices__text">' . esc_html__("These FAQs are from the", "helpie-faq") . ' <b>"' . $group->name . '"</b> Group. You can edit this FAQ Group <a class="helpie-notices__link" href="' . esc_url($group_link) . '">' . esc_html__("here", "helpie-faq") . '</a>.</p>';
            // $html .= '<p class="helpie-notices__text">' . esc_html__("If you want to display all the FAQs from multiple groups then remove the group id from the shortcode", "helpie-faq") . ' <a class="helpie-notices__link" href="' . esc_url($edit_post_link) . '">' . esc_html__("here", "helpie-faq") . '</a>.</p>';
            $html .= '<p class="helpie-notices__text">' . esc_html__("To remove this ", "helpie-faq") . '<b>admin-only</b> ' . esc_html__("notice", "helpie-faq") . ' 1) ' . esc_html__("Edit this page", "helpie-faq") . ' 2) ' . esc_html__("Remove the shortcode ", "helpie-faq") . $notice_shortcode_content . '</p>';
            $html .= '<p></p>';
            $html .= '</div>';

            return $html;
        }
    }
}
