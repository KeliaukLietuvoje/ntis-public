<?php
class NTIS_Contact_Form
{
    public static $id = 0;
    public function __construct()
    {
        add_shortcode('ntis_contact_form', array($this, 'contact_form_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_send_contact_form', array($this, 'send_contact_form'));
        add_action('wp_ajax_nopriv_send_contact_form', array($this, 'send_contact_form'));
    }
    public function enqueue_scripts()
    {
        wp_enqueue_style('ntis-contact-form', NTIS_THEME_URL . '/inc/shortcodes/contact-form/assets/contact-form.css');
        wp_enqueue_script('ntis-contact-form', NTIS_THEME_URL . '/inc/shortcodes/contact-form/assets/contact-form.js', array('jquery'), null, true);
        wp_localize_script('ntis-contact-form', 'ntis_contact_form', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ntis_contact_form_nonce')
        ));
    }
    public function send_contact_form()
    {
        if(!check_ajax_referer('ntis_contact_form_nonce', 'ntis_nonce', false)) {
            $response = array(
                'success' => false,
                'console' => 'Invalid nonce.',
                'message' => __('Formos pildymo klaida.', 'ntis')
            );
            wp_send_json($response);
        }
        if(!is_email($_POST['ntis_email'])) {
            $response = array(
                'success' => false,
                'console' => 'Invalid email address.',
                'message' => __('Neteisingas el. pašto adresas.', 'ntis')
            );
            wp_send_json($response);
        }

        $phone = sanitize_text_field($_POST['ntis_phone']);
        $email = sanitize_email($_POST['ntis_email']);
        $message = sanitize_textarea_field($_POST['ntis_message']);

        $template_model = array(
            'phone' => $phone,
            'email' => $email,
            'message' => $message,
        );
        
        if(!class_exists('NTIS_Postmark')) {
            $response = array(
                'success' => false,
                'console' => 'NTIS_POSTMARK plugin not enabled.',
                'message' => __('Įvyko sisteminė klaida siunčiant Jūsų klausimą.', 'ntis')
            );
        } else {
            $response = NTIS_Postmark::send_email('info@ntis.lt', 35679775, $template_model);
            $response = json_decode($response, true);
            if(isset($response['ErrorCode'])) {
                $response = array(
                    'success' => false,
                    'console' => $response,
                    'message' => __('Įvyko sisteminė klaida siunčiant Jūsų klausimą.', 'ntis')
                );
            } else {
                $response = array(
                    'success' => true,
                    'console' => '',
                    'message' => __('Jūsų žinutė sėkmingai išsiųsta.', 'ntis')
                );
            }
            wp_send_json($response);
        }
    }
    public function contact_form_shortcode()
    {
        $privacy_policy = get_option('wp_page_for_privacy_policy');
        if($privacy_policy) {
            $privacy_policy = get_permalink($privacy_policy);
        } else {
            $privacy_policy = '#';
        }

        self::$id++;

        ob_start();
        $action = admin_url('admin-ajax.php');
        ?>
        <form method="post" action="<?php echo $action; ?>" class="ntis-contact-form needs-validation" novalidate>
            <input type="hidden" name="action" value="send_contact_form">  
            <input type="hidden" name="ntis_nonce" value="<?php echo wp_create_nonce('ntis_contact_form_nonce');?>">
            <div class="row">
                <div>
                    <label for="ntis_phone_<?php echo self::$id;?>" class="ntis-form-label"><?php _e('Telefono numeris', 'ntis');?></label>
                    <input type="tel" id="ntis_phone_<?php echo self::$id;?>" name="ntis_phone" class="ntis-form-control" required>
                    <div class="invalid-feedback"><?php _e('Prašome nurodyti telefono numerį', 'ntis');?></div>
                </div>
                <div>
                    <label for="ntis_email_<?php echo self::$id;?>" class="ntis-form-label"><?php _e('El. paštas', 'ntis');?></label>
                    <input type="email" id="ntis_email_<?php echo self::$id;?>" name="ntis_email" class="ntis-form-control" required>
                    <div class="invalid-feedback"><?php _e('Prašome nurodyti el.pašto adresą', 'ntis');?></div>
                </div>
            </div>
            <div>
                <label for="ntis_message_<?php echo self::$id;?>" class="ntis-form-label"><?php _e('Klausimas', 'ntis');?></label>
                <textarea id="ntis_message_<?php echo self::$id;?>" name="ntis_message" rows="4" cols="50" class="ntis-form-control" required></textarea>
                <div class="invalid-feedback"><?php _e('Prašome įvesti klausimą', 'ntis');?></div>
            </div>
            <div class="ntis-form-check">
                <input type="checkbox" name="bdar" value="yes" class="ntis-form-check-input" id="ntis_bdar_<?php echo self::$id;?>" required/> 
                <label class="ntis-form-check-label" for="ntis_bdar_<?php echo self::$id;?>">
                <?php echo sprintf(__('Su UAB „Keliauk Lietuvoje“ <a href="%s">privatumo politika</a> susipažinau ir sutinku, kad mano asmens duomenys būtų tvarkomi užklausos vykdymo tikslais.', 'ntis'), $privacy_policy);?>
                </label>
                <div class="invalid-feedback"><?php _e('Prašome įvesti klausimą', 'ntis');?></div>
            </div>
            <div>
                <button type="submit"><?php _e('Pateikti', 'ntis');?></button>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }
}
new NTIS_Contact_Form();
