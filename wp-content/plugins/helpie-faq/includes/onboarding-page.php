<?php

namespace HelpieFaq\Includes;

if (!class_exists('\HelpieFaq\Includes\Onboarding_Page')) {
    class Onboarding_Page
    {
        public function render()
        {
            echo '<div id="helpie-faq-onboarding-page"></div>';
        }
    }
}
