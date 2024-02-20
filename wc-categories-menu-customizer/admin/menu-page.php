<?php

// Добавляем обработчик действия при отправке формы
if (isset($_POST['brs_menu_name'])) {
    brs_create_or_update_woocommerce_categories_menu(sanitize_text_field($_POST['brs_menu_name']));
    add_action('admin_notices', 'brs_admin_notice_success');
}

// Функция для вывода уведомления об успешном создании/обновлении меню
function brs_admin_notice_success() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e('Меню успешно создано/обновлено!', 'woocommerce-categories-menu-creator'); ?></p>
    </div>
    <?php
}

function brs_create_or_update_woocommerce_categories_menu($menu_name) {
    $menu_object = wp_get_nav_menu_object($menu_name);
    
    if (!$menu_object) {
        $menu_id = wp_create_nav_menu($menu_name);
        if (is_wp_error($menu_id)) {
            error_log('Ошибка при создании меню: ' . $menu_id->get_error_message());
            return;
        }
    } else {
        $menu_id = $menu_object->term_id;
    }

    $root_categories = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
        'parent' => 0
    ]);

    if (is_wp_error($root_categories)) {
        error_log('Ошибка при получении родительских категорий: ' . $root_categories->get_error_message());
        return;
    }

    foreach ($root_categories as $category) {
        brs_add_category_to_menu($category, $menu_id);
    }
}

function brs_add_category_to_menu($category, $menu_id, $parent_menu_item_id = 0) {
    $existing_items = wp_get_nav_menu_items($menu_id, [
        'meta_key' => '_menu_item_object_id',
        'meta_value' => $category->term_id
    ]);

    if (empty($existing_items)) {
        $menu_item_data = [
            'menu-item-title' => $category->name,
            'menu-item-url' => get_term_link($category->term_id),
            'menu-item-status' => 'publish',
            'menu-item-type' => 'taxonomy',
            'menu-item-object' => 'product_cat',
            'menu-item-object-id' => $category->term_id,
            'menu-item-parent-id' => $parent_menu_item_id
        ];
        
        $menu_item_id = wp_update_nav_menu_item($menu_id, 0, $menu_item_data);
        
        if (is_wp_error($menu_item_id)) {
            error_log('Ошибка при добавлении категории в меню: ' . $menu_item_id->get_error_message());
            return;
        }
    }

    $child_categories = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
        'parent' => $category->term_id
    ]);

    if (is_wp_error($child_categories)) {
        error_log('Ошибка при получении дочерних категорий: ' . $child_categories->get_error_message());
        return;
    }

    foreach ($child_categories as $child_category) {
        brs_add_category_to_menu($child_category, $menu_id, $menu_item_id);
    }
}

function brs_woocommerce_categories_menu_creator_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form method="post">
            <table class="form-table">
                <tr valign="top">
                <th scope="row"><?php _e('Название меню:', 'woocommerce-categories-menu-creator'); ?></th>
                <td><input type="text" name="brs_menu_name" value="woocommerce_categories_menu" /></td>
                </tr>
            </table>
            <?php submit_button(__('Создать/Обновить меню', 'woocommerce-categories-menu-creator')); ?>
        </form>
    </div>
    <?php
}
