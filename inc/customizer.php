<?php
/** Personal information editable in Appearance > Customize. */
defined('ABSPATH') || exit;

function monty_portfolio_defaults()
{
    return array(
        'name' => 'Monty Chaudhary',
        'first_name' => 'Monty',
        'eyebrow' => 'FRONT-END & WORDPRESS · LEEDS, UK',
        'hero_description' => 'Front-end and WordPress developer in Leeds. Business websites, interactive ideas, and the code that makes them work.',
        'availability' => 'Open to junior web development opportunities',
        'about_lead' => "A project management background.\nA growing collection of websites.",
        'about_one' => 'I’m Monty. I’ve worked on websites for local business concepts, a client WordPress build, interactive JavaScript projects and my own service marketplace idea.',
        'about_two' => 'I like having something concrete to work on: a brief to understand, a layout to get right, or an interaction that needs one more adjustment. My MSc in Project Management helps me organise that process.',
        'education' => 'MSc Project Management',
        'university' => 'Leeds Beckett University',
        'email' => 'letsmailmonti@gmail.com',
        'github' => 'https://github.com/stocker7767',
        'linkedin' => 'https://www.linkedin.com/in/monty-chaudhary-37b849290/',
        'location' => 'LEEDS, UK',
        'contact_location' => 'BASED IN LEEDS · OPEN TO OPPORTUNITIES',
        'portrait' => '',
    );
}

function monty_portfolio_setting($key)
{
    $defaults = monty_portfolio_defaults();
    return (string) get_theme_mod('monty_' . $key, $defaults[$key] ?? '');
}

function monty_portfolio_portrait()
{
    $portrait = monty_portfolio_setting('portrait');
    return $portrait ?: get_theme_file_uri('assets/images/monty.png');
}

function monty_portfolio_customize($customizer)
{
    $customizer->add_section('monty_details', array(
        'title' => __('Portfolio details', 'monty-portfolio'),
        'description' => __('Update your introduction, portrait, contact links and about text. Manage the gallery under Projects in the dashboard.', 'monty-portfolio'),
        'priority' => 30,
    ));
    $fields = array(
        'name' => array('Full name', 'text'),
        'first_name' => array('Name in the large hero heading', 'text'),
        'eyebrow' => array('Role and location above the heading', 'text'),
        'hero_description' => array('Introduction', 'textarea'),
        'availability' => array('Availability message', 'text'),
        'about_lead' => array('About: opening lines', 'textarea'),
        'about_one' => array('About: first paragraph', 'textarea'),
        'about_two' => array('About: second paragraph', 'textarea'),
        'education' => array('Qualification', 'text'),
        'university' => array('University', 'text'),
        'email' => array('Contact email', 'email'),
        'github' => array('GitHub profile URL', 'url'),
        'linkedin' => array('LinkedIn profile URL', 'url'),
        'location' => array('Location on the portrait', 'text'),
        'contact_location' => array('Location and availability above contact', 'text'),
    );
    $defaults = monty_portfolio_defaults();
    foreach ($fields as $key => $field) {
        $sanitize = 'text' === $field[1] ? 'sanitize_text_field' : ('textarea' === $field[1] ? 'sanitize_textarea_field' : ('email' === $field[1] ? 'sanitize_email' : 'esc_url_raw'));
        $customizer->add_setting('monty_' . $key, array('default' => $defaults[$key], 'sanitize_callback' => $sanitize, 'transport' => 'refresh'));
        $customizer->add_control('monty_' . $key, array('label' => $field[0], 'section' => 'monty_details', 'type' => $field[1]));
    }
    $customizer->add_setting('monty_portrait', array('default' => '', 'sanitize_callback' => 'esc_url_raw'));
    $customizer->add_control(new WP_Customize_Image_Control($customizer, 'monty_portrait', array('label' => __('Portrait', 'monty-portfolio'), 'section' => 'monty_details', 'description' => __('Leave empty to use the included portrait.', 'monty-portfolio'))));
}
add_action('customize_register', 'monty_portfolio_customize');
