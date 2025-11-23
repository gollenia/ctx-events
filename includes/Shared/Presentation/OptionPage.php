<?php

namespace Contexis\Events\Shared\Presentation;

final class OptionsPage
{
    public function register(): void
    {
        // Menüeintrag
        add_action('admin_menu', function () {
            add_submenu_page(
                'edit.php?post_type=ctx-events',          // Parent = dein CPT-Menü
                __('Events Einstellungen', 'ctx-events'), // Seitentitel
                __('Einstellungen', 'ctx-events'),        // Menü-Label
                'manage_options',                         // Capability
                'ctx-events-options',                     // Menü-Slug
                [$this, 'render'],                        // Callback
                20                                        // Position (optional)
            );
        });
    }

    public function render(): void
    {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Events Einstellungen', 'ctx-events') . '</h1>';
        echo '<div id="ctx-events-options-root"></div>';
        echo '</div>';
    }
}
