<?php
/**
 * Plugin Name:  Bucket
 * Description:  Media file connect to minio
 * Version:      1.0.3
 * Author:       Petras Pauliūnas
 * Author URI:   mailto:petras.pauliunas@gmail.com
 */

define('AS3CF_SETTINGS', serialize(array(
    'provider' => 'aws',
    'access-key-id' => MINIO_ACCESSKEY,
    'secret-access-key' => MINIO_SECRETKEY,
)));

function minio_s3_client_args($args)
{
    $args['endpoint'] = 'http://' . MINIO_ENDPOINT;
    $args['use_path_style_endpoint'] = true;
    return $args;
}
add_filter('as3cf_aws_s3_client_args', 'minio_s3_client_args');


add_filter('as3cf_aws_s3_url_domain', 'minio_s3_url_domain', 10, 2);
function minio_s3_url_domain($domain, $bucket)
{
    return BUCKET_URL . '/' . $bucket;
}
