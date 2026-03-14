/* LOCAL TO PROD */
UPDATE wpjoo_options SET option_value = replace(option_value, 'designhg.loc', 'designhg.cz')
WHERE option_name = 'home' OR option_name = 'siteurl';
UPDATE wpjoo_posts SET guid = replace(guid, 'designhg.loc', 'designhg.cz');
UPDATE wpjoo_posts SET post_content = replace(post_content, 'designhg.loc', 'designhg.cz');
UPDATE wpjoo_postmeta SET meta_value = replace(meta_value, 'designhg.loc', 'designhg.cz');

/* PROD TO LOCAL */
UPDATE wpjoo_options SET option_value = replace(option_value, 'designhg.cz', 'designhg.loc')
WHERE option_name = 'home' OR option_name = 'siteurl';
UPDATE wpjoo_posts SET guid = replace(guid, 'designhg.cz', 'designhg.loc');
UPDATE wpjoo_posts SET post_content = replace(post_content, 'designhg.cz', 'designhg.loc');
UPDATE wpjoo_postmeta SET meta_value = replace(meta_value, 'designhg.cz', 'designhg.loc');

/* DISABLE ALL PLUGINS */
UPDATE wpjoo_options
SET option_value = 'a:0:{}'
WHERE option_name = 'active_plugins';
