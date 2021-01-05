<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Thinkmod Child backgrounds callbacks.
 *
 * @package    theme_thinkmod_child
 * @copyright  2020 Will Nahmens
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

/**
 * Returns the main SCSS content.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_thinkmod_child_get_main_scss_content($theme) {
    global $CFG;

    $scss = '';
    $filename = !empty($theme->settings->preset) ? $theme->settings->preset : null;
    $fs = get_file_storage();

    $context = context_system::instance();
    if ($filename == 'default.scss') {
        // We still load the default preset files directly from the boost theme. No sense in duplicating them.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    } else if ($filename == 'plain.scss') {
        // We still load the default preset files directly from the boost theme. No sense in duplicating them.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/plain.scss');

    } else if ($filename && ($presetfile = $fs->get_file($context->id, 'theme_thinkmod_child', 'preset', 0, '/', $filename))) {
        // This preset file was fetched from the file area for theme_photo and not theme_boost (see the line above).
        $scss .= $presetfile->get_content();
    } else {
        // Safety fallback - maybe new installs etc.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    }

    // Moove scss.
    $moovevariables = file_get_contents($CFG->dirroot . '/theme/moove/scss/moove/_variables.scss');
    $moove = file_get_contents($CFG->dirroot . '/theme/moove/scss/moove.scss');
 
    // Pre CSS - this is loaded AFTER any prescss from the setting but before the main scss.                                        
    $pre = file_get_contents($CFG->dirroot . '/theme/thinkmod_child/scss/pre.scss');                                                         
    // Post CSS - this is loaded AFTER the main scss but before the extra scss from the setting.                                    
    $post = file_get_contents($CFG->dirroot . '/theme/thinkmod_child/scss/post.scss');                                                       
 
    // Combine them together.                                                                                                       
    return $moovevariables . "\n" .$pre . "\n" . $scss . "\n" . $moove . "\n" . $post; 
}

/**
 * Get SCSS to prepend.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_thinkmod_child_get_pre_scss($theme) {
    // Load the settings from the parent.                                                                                           
    $theme = theme_config::load('moove');                                                                                           
    // Call the parent themes get_pre_scss function.                                                                                
    return theme_boost_get_pre_scss($theme); 
}

// Function to return the SCSS to append to our main SCSS for this theme.
// Note the function name starts with the component name because this is a global function and we don't want namespace clashes.
function theme_thinkmod_child_get_extra_scss($theme) {
    // Load the settings from the parent.                                                                                           
    $theme = theme_config::load('moove');                                                                                           
    // Call the parent themes get_extra_scss function.                                                                                
    return theme_moove_get_extra_scss($theme);                         
} 

/**
 * Copy the updated theme image to the correct location in dataroot for the image to be served
 * by /theme/image.php. Also clear theme caches.
 *
 * @param $settingname
 */
function theme_thinkmod_child_update_settings_images($settingname) {
    global $CFG;

    // The setting name that was updated comes as a string like 's_theme_photo_loginbackgroundimage'.
    // We split it on '_' characters.
    $parts = explode('_', $settingname);
    // And get the last one to get the setting name..
    $settingname = end($parts);

    // Admin settings are stored in system context.
    $syscontext = context_system::instance();
    // This is the component name the setting is stored in.
    $component = 'theme_thinkmod_child';


    // This is the value of the admin setting which is the filename of the uploaded file.
    $filename = get_config($component, $settingname);
    // We extract the file extension because we want to preserve it.
    $extension = substr($filename, strrpos($filename, '.') + 1);

    // This is the path in the moodle internal file system.
    $fullpath = "/{$syscontext->id}/{$component}/{$settingname}/0{$filename}";

    // This location matches the searched for location in theme_config::resolve_image_location.
    //$pathname = $CFG->dataroot . '/pix_plugins/theme/thinkmod_child/' . $settingname . '.' . $extension;
    $pathname = $CFG->dirroot . '/theme/thinkmod_child/pix/' . $settingname . '.' . $extension;


    // This pattern matches any previous files with maybe different file extensions.
    //$pathpattern = $CFG->dataroot . '/pix_plugins/theme/thinkmod_child/' . $settingname . '.*';
    $pathpattern = $CFG->dirroot . '/theme/thinkmod_child/pix/' . $settingname . '.*';

    // Make sure this dir exists.
    //@mkdir($CFG->dataroot . '/pix_plugins/theme/thinkmod_child/', $CFG->directorypermissions, true);
    @mkdir($CFG->dirroot . '/theme/thinkmod_child/pix/', $CFG->directorypermissions, true);



    // Delete any existing files for this setting.
    foreach (glob($pathpattern) as $filename) {
        @unlink($filename);
    }

    // Get an instance of the moodle file storage.
    $fs = get_file_storage();
    // This is an efficient way to get a file if we know the exact path.
    if ($file = $fs->get_file_by_hash(sha1($fullpath))) {
        // We got the stored file - copy it to dataroot.
        $file->copy_content_to($pathname);
    }

    // Reset theme caches.
    theme_reset_all_caches();
}


function theme_thinkmod_child_page_init(moodle_page $page) {
    global $CFG;
    global $course;
    global $PAGE;

    //$modinfo = get_fast_modinfo($course);
    //var_dump($modinfo->get_section_info_all());

    /*foreach ($modinfo->get_section_info_all() as $section => $thissection) {
        var_dump($section);
    }*/

    //die();

    /*$divisorshow = false;
    $count = 1;
    $currentdivisor = 1;
    $modinfo = get_fast_modinfo($course);
    $inline = '';
    foreach ($modinfo->get_section_info_all() as $section => $thissection) {
        var_dump($course);
        die();
        if ($section == 0) {
            continue;
        }
        if ($section > $course->numsections) {
            continue;
        }
        if ($course->hiddensections && !(int)$thissection->visible) {
            continue;
        }
        if (isset($course->{'divisor' . $currentdivisor}) &&
            $count > $course->{'divisor' . $currentdivisor}) {
            var_dump($course->{'divisor' . $currentdivisor});
            $count = 1;
        }
        if (isset($course->{'divisor' . $currentdivisor}) &&
            $course->{'divisor' . $currentdivisor} != 0 &&
            !isset($divisorshow[$currentdivisor])) {
            $currentdivisorhtml = format_string($course->{'divisortext' . $currentdivisor});
            $currentdivisorhtml = str_replace('[br]', '<br>', $currentdivisorhtml);
            $currentdivisorhtml = html_writer::tag('div', $currentdivisorhtml, ['class' => 'divisortext']);
            if ($course->inlinesections) {
                $inline = 'inlinebuttonsections';
            }
            $html .= html_writer::tag('div', $currentdivisorhtml, ['class' => "divisorsection $inline"]);
            $divisorshow[$currentdivisor] = true;
        }
        $id = 'buttonsection-' . $section;
        if ($course->sequential) {
            $name = $section;
        } else {
            if (isset($course->{'divisor' . $currentdivisor}) &&
            $course->{'divisor' . $currentdivisor} == 1) {
                $name = '&bull;&bull;&bull;';
            } else {
                $name = $count;
            }
        }
        if ($course->sectiontype == 'alphabet' && is_numeric($name)) {
            $name = $this->number_to_alphabet($name);
        }
        if ($course->sectiontype == 'roman' && is_numeric($name)) {
            $name = $this->number_to_roman($name);
        }
        $class = 'buttonsection';
        $onclick = 'M.format_buttons.show(' . $section . ',' . $course->id . ')';
        if (!$thissection->available &&
            !empty($thissection->availableinfo)) {
            $class .= ' sectionhidden';
        } else if (!$thissection->uservisible || !$thissection->visible) {
            $class .= ' sectionhidden';
            $onclick = false;
        }
        if ($course->marker == $section) {
            $class .= ' current';
        }
        if ($sectionvisible == $section) {
            $class .= ' sectionvisible';
        }
        if ($PAGE->user_is_editing()) {
            $onclick = false;
        }
        $count++;
    }
    die();*/

    $mobilestyles = $CFG->mobilecssurl;

    //need to check which course we are in
    global $COURSE;
    $course_id = $COURSE->id;
    if($course_id > 1) {
        $context = context_course::instance($course_id);
        $courseSelector = 'courseColor'.$course_id;
        $coursecolor = get_config('theme_thinkmod_child', $courseSelector);
    } else {
        $coursecolor = false;
    }

    if($coursecolor) {
        echo '<style>
            .test-color {
                color: ' . $coursecolor . '99;
            }

            #buttonsectioncontainer .buttonsection.current {
                background-color:' . $coursecolor . ';
            }

            #buttonsectioncontainer .buttonsection.sectionvisible {
                background-color:' . $coursecolor . ';
            }

            .otp-module-local {
                fill:' . $coursecolor . ' !important; 
                color:' . $coursecolor . ';
            }

            .btn-primary { 
                color: #fff !important;
                background-color:' . $coursecolor . ';
                border-color:' . $coursecolor . ';
            }

            .btn-primary:hover { 
                color: #fff !important;
                background-color:' . $coursecolor . 'aa;
                border-color:' . $coursecolor . ';
            }

            .pagelayout-course {
                background-color:' . $coursecolor . '09;
            }

            h1,h2,h3,h4,h5 {
                color:' . $coursecolor . ';
            }

            .inplaceeditable .quickeditlink {
                color:' . $coursecolor . ';
            }

            a,a:active,a:visited {
                color:' . $coursecolor . ';
            }

            h1,h2,h3,h4,h5 a,a:visited {
                color:' . $coursecolor . ';
            }
            #sidepreopen-control {
                background-color:' . $coursecolor . ';
            }


            .pagelayout-course #page-header {
                color:' . $coursecolor . '; 
            }

            .drawer-toggle, #nav-drawer, #nav-drawer ul {
                background-color:' . $coursecolor . ' !important;
            }

            nav.navbar ul.navbar-nav .popover-region .popover-region-toggle .icon {
                line-height: 70px;
            }

        </style>';
    } 
}

function theme_thinkmod_child_before_footer() {
    
}

function thinkmod_child_extend_settings_navigation($navigation, $context) {
    global $CFG;
    $parent = $navigation->find('courseadmin', navigation_node::TYPE_COURSE);

    if($parent == null) {
        return;
    }

    $parent->add('Set course color', '/admin/settings.php?section=themesettingthinkmod_child');
}

