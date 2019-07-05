<?php
/**
 * @package EDK
 */

if (!class_exists('options'))
{
    exit('This killboard is not supported (options package missing)!');
}
options::cat('Modules', 'SRP status', 'Settings');
options::fadd('SRP status service URL', 'srp_status_url', 'edit:size:128:maxlength:255');
options::fadd('Max number of redirects', 'srp_status_redirs', 'edit:size:3' );
