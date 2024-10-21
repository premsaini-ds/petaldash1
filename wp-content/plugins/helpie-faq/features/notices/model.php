<?php

namespace HelpieFaq\Features\Notices;

if (!class_exists('\HelpieFaq\Features\Notices\Model')) {
    class Model
    {
        public function get_group($group_id)
        {
            $group = get_term($group_id);
            return $group;
        }

    }
}