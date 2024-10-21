<?php

namespace HelpieFaq\Includes;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Includes\Lms_Integrations')) {

    class Lms_Integrations
    {

        public function __construct()
        {
            // error_log('Lms_Integrations : ');

            // LearnPress Hooks - Course
            add_filter('learn-press/course-tabs', array($this, 'courses_tabs'), 10, 1);
            // add_filter('the_content', array($this, 'lessons_faq'), 1);

            // add_action('tutor_course_instructors_html', array($this, 'tutorlms_courses'));
            // add_action('tutor_course_metabox_before_additional_data', array($this, 'tutorlms_courses'));

            // TutorLMS Hooks - Course
            add_filter('tutor_course/single/content', array($this, 'tutorlms_courses'));

            // TutorLMS Hooks - Lesson
            add_filter('tutor_lesson/single/content', array($this, 'tutorlms_courses'));
            // add_filter('tutor_lesson/single/lesson_sidebar', array($this, 'tutorlms_lesson_sidebar'));

            // Learndash - Course Page hooks
            add_action('learndash-course-after', array($this, 'learndash_course'));
            // add_action('learndash-course-content-list-after', array($this, 'learndash_course'));
            // add_action('learndash-course-before', array($this, 'learndash_course'));

            // Learndash - Lesson Page hooks
            // add_action('learndash-lesson-before', array($this, 'learndash_course'));
            add_action('learndash-lesson-after', array($this, 'learndash_course'));

            $this->set_global_settings();
        }

        public function set_global_settings()
        {
        }

        public function setup()
        {
            $lms_default_args = [
                'show_title' => true,
                'title' => "FAQs",
                'title_tag' => 'h5',
                'show_search' => true,
                'limit' => -1,
                'display_mode' => 'simple_accordion',
                'integration' => 'lms',
                'search_placeholder' => "Search",
                'display_mode_group_by' => 'none',
            ];
            $faq_model = new \HelpieFaq\Features\Faq\Faq_Model();
            $global_args = $faq_model->get_default_args();

            $this->args = [];

            foreach ($lms_default_args as $key => $value) {

                $this->args[$key] = isset($global_args[$key]) ? $global_args[$key] : $value;
                // error_log("key: " . $key . " - global: " . $global_args[$key]);
                // error_log('$this->args: ' . print_r($this->args, true));
            }

            // error_log('$lms_default_args: ' . print_r($lms_default_args, true));
            // error_log('$global_args: ' . print_r($global_args, true));
            // error_log('$this->args: ' . print_r($this->args, true));
            // $lms_args = ['show_title', 'title']
        }

        public function learndash_course()
        {
            // error_log('learndash_course');
            $this->print_faq_html();
            // $this->get_faq_html();

        }

        public function tutorlms_lesson_sidebar($content)
        {
            $faq_html = '<div class="tutor-accordion-item-header" tutor-course-single-topic-toggler="">';
            $faq_html .= 'FAQs';
            $faq_html .= '</div>';
            $faq_html .= '<div class="tutor-accordion-item-body" style=" display: block;">';
            $faq_html .= $this->get_faq_html();
            $faq_html .= '</div>';

            $content = $content . $faq_html;

            return $content;
        }

        public function tutorlms_courses($content)
        {
            // error_log('tutor_course_loop_after_content');
            $content = $content . $this->get_faq_html();

            return $content;
        }

        public function courses_tabs($defaults)
        {
            // error_log('courses_tabs $defaults : ' . print_r($defaults, true));
            $faq_tab = array(
                'title' => 'Helpie FAQs',
                'priority' => 50,
                'callback' => array($this, 'print_faq_html'),
            );
            $defaults[] = $faq_tab;
            return $defaults;
        }

        public function lessons_faq($content)
        {
            // error_log('lessons_faq $content : ' . print_r($content, true));
            $faq_html = $this->get_faq_html();
            $content = $content . $faq_html;
            return $content;
        }

        public function print_faq_html()
        {
            hfaq_safe_echo($this->get_faq_html());
        }

        public function get_faq_html()
        {
            $this->setup();
            $args = $this->args;

            // Get configured post ids
            $faq_posts = $this->get_faqs_of_post();

            $faq = new \HelpieFaq\Features\Faq\Faq();
            $args['items_wp_objs'] = $faq_posts;
            // Default Settings
            $defaults = $faq->model->get_default_args();
            $args = array_merge($defaults, $args);

            // Important Overrides
            $args['display_mode_group_by'] = 'none';

            // TODO: But follow global settings
            return $faq->get_view($args);
        }

        public function get_faqs_of_post($post_id = 0)
        {
            $post_id = get_the_id();
            // error_log('post_id: ' . $post_id);

            $integration = new \HelpieFaq\Includes\Integration();
            $post_ids = $integration->get_post_ids_from_group_settings($post_id);
            $posts = $integration->get_posts_by_ids($post_ids);

            return $posts;
        }
    }
}
