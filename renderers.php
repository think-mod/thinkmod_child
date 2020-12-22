<?php

// This line protects the file from being accessed by a URL directly.                                                               
defined('MOODLE_INTERNAL') || die();
 
require_once($CFG->dirroot . '/course/renderer.php');


class theme_thinkmod_child_core_renderer extends \theme_boost\output\core_renderer {
    /**
     * Wrapper for header elements.
     *
     * @return string HTML to display the main header.
     */
    public function full_header() {
        global $COURSE;
        global $CFG;

        $course_id = $COURSE->id;

        $theme = theme_config::load('thinkmod_child');
        $context = context_course::instance($course_id);


        if ($this->page->include_region_main_settings_in_header_actions() &&
                !$this->page->blocks->is_block_present('settings')) {
            // Only include the region main settings if the page has requested it and it doesn't already have
            // the settings block on it. The region main settings are included in the settings block and
            // duplicating the content causes behat failures.
            $this->page->add_header_action(html_writer::div(
                $this->region_main_settings_menu(),
                'd-print-none',
                ['id' => 'region-main-settings-menu']
            ));
        }

        $header = new stdClass();
        $header->settingsmenu = $this->context_header_settings_menu();
        $header->contextheader = $this->context_header();
        $header->hasnavbar = empty($this->page->layout_options['nonavbar']);
        $header->navbar = $this->navbar();
        $header->pageheadingbutton = $this->page_heading_button();
        $header->courseheader = $this->course_header();
        $header->headeractions = $this->page->get_header_actions();

        $course_svg = get_config('theme_thinkmod_child', 'coursecustomsvg'.$course_id);

        $course_svg = $theme->setting_file_url('coursecustomsvg' . $course_id, 'coursecustomsvg' . $course_id);


        //$course_svg = $CFG->dataroot .'\/pluginfile.php/'.$forumcontextid.'/mod_forum/post/$postid/image.jpg';


        if($course_svg) {
            $header->watermark = $course_svg;
           // $header->watermark = $CFG->dataroot . '/pluginfile.php/pix_plugins/theme/thinkmod_child/coursecustomsvg' . $course_id . '.svg';
        }

        //"C:\xampp\apps\moodle\moodledata\pix_plugins\theme\thinkmod_child\coursecustomsvg2.svg"

        return $this->render_from_template('core/full_header', $header);
    }
}
