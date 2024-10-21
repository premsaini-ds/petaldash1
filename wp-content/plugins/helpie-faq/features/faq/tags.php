<?php

namespace HelpieFaq\Features\Faq;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('HelpieFaq\Features\Faq\Tags')) {
    class Tags
    {
        public function get_tags(array $itemProps)
        {
            if (empty($itemProps)) {
                return $itemProps;
            }

            $post_id = $itemProps['post_id'];

            if (empty($post_id)) {
                return $itemProps;
            }
            //get the post tags
            $tags = get_the_tags($post_id);
            $tags = isset($tags) && !empty($tags) ? $tags : [];
            $itemProps['tags'] = $this->convert_tags_into_string($tags);

            return $itemProps;
        }

        public function convert_tags_into_string(array $tags = array())
        {
            if (empty($tags)) {
                return '';
            }
            $tag_names = [];
            foreach ($tags as $tag) {
                $tag_names[] = $tag->name;
            }

            return implode(',', $tag_names);
        }
    }
}