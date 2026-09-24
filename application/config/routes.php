<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'home';
$route['404_override'] = 'errors/page_missing';
$route['translate_uri_dashes'] = FALSE;

/*
| -------------------------------------------------------------------------
| Sta. Monica Parish Connect - Route Map
| -------------------------------------------------------------------------
*/

// ---- Public site ----
$route['mass-schedule']              = 'home/mass_schedule';
$route['sacraments']                 = 'home/sacraments';
$route['announcements']              = 'home/announcements';
$route['announcements/(:any)']       = 'home/announcement_detail/$1';
$route['events']                     = 'home/events';
$route['events/(:any)']              = 'home/event_detail/$1';
$route['ministries']                 = 'home/ministries';
$route['ministries/(:any)']          = 'home/ministry_detail/$1';
$route['priests']                    = 'home/priests';
$route['prayers']                    = 'home/prayers';
$route['st-monica']                  = 'home/st_monica';
$route['about']                      = 'home/about';
$route['contact']                    = 'home/contact';
$route['donate']                     = 'home/donate';
$route['verify/(:any)']              = 'home/verify_certificate/$1';

// ---- Auth ----
$route['login']                      = 'auth/login';
$route['register']                   = 'auth/register';
$route['logout']                     = 'auth/logout';
$route['forgot-password']            = 'auth/forgot_password';

// ---- Parishioner area (prefix: my) ----
$route['my/dashboard']               = 'parishioner/dashboard';
$route['my/bookings']                = 'parishioner/booking';
$route['my/bookings/datatable']      = 'parishioner/booking/datatable';
$route['my/bookings/store']          = 'parishioner/booking/store';
$route['my/bookings/availability']   = 'parishioner/booking/availability';
$route['my/bookings/new/(:any)']     = 'parishioner/booking/create/$1';
$route['my/bookings/cancel/(:num)']  = 'parishioner/booking/cancel/$1';
$route['my/bookings/(:num)']         = 'parishioner/booking/view/$1';
$route['my/certificates']            = 'parishioner/certificate';
$route['my/certificates/new']        = 'parishioner/certificate/create';
$route['my/certificates/store']      = 'parishioner/certificate/store';
$route['my/certificates/(:num)']     = 'parishioner/certificate/view/$1';
$route['my/payments']                = 'parishioner/payment';
$route['my/payments/pay/(:any)/(:num)'] = 'parishioner/payment/pay/$1/$2';
$route['my/payments/submit']         = 'parishioner/payment/submit';
$route['my/profile']                 = 'parishioner/profile';
$route['my/profile/update']          = 'parishioner/profile/update';
$route['my/profile/change-password'] = 'parishioner/profile/change_password';
$route['my/mass-intentions']         = 'parishioner/mass_intention';
$route['my/mass-intentions/store']   = 'parishioner/mass_intention/store';
$route['staff/mass-intention-reader'] = 'staff/mass_intention/reader';
$route['staff/mass-intention-reader/complete'] = 'staff/mass_intention/complete_mass';
$route['staff/mass-intention/assign-mass'] = 'staff/mass_intention/assign_mass';
$route['priest/mass-intentions']      = 'priest/mass_intention';
$route['priest/mass-intentions/reader'] = 'priest/mass_intention/reader';

// ---- Admin area ----
$route['admin/service_type/get/(:num)']                    = 'admin/service_type/get/$1';
$route['admin/service_type/store']                         = 'admin/service_type/store';
$route['admin/service_type/add-requirement']               = 'admin/service_type/add_requirement';
$route['admin/service_type/delete-requirement/(:num)']     = 'admin/service_type/delete_requirement/$1';
$route['admin/service_type/save-schedule-rule']            = 'admin/service_type/save_schedule_rule';
$route['admin/service_type/delete-schedule-rule/(:num)']   = 'admin/service_type/delete_schedule_rule/$1';
$route['admin']                      = 'admin/dashboard';
$route['admin/(:any)']               = 'admin/$1';

// ---- Secretary area ----
$route['staff/service-config']                         = 'admin/service_type';
$route['staff/service-config/get/(:num)']             = 'admin/service_type/get/$1';
$route['staff/service-config/store']                   = 'admin/service_type/store';
$route['staff/service-config/add-requirement']         = 'admin/service_type/add_requirement';
$route['staff/service-config/delete-requirement/(:num)'] = 'admin/service_type/delete_requirement/$1';
$route['staff/service-config/save-schedule-rule']      = 'admin/service_type/save_schedule_rule';
$route['staff/service-config/delete-schedule-rule/(:num)'] = 'admin/service_type/delete_schedule_rule/$1';
$route['staff']                      = 'staff/dashboard';
$route['staff/(:any)']               = 'staff/$1';

// ---- Priest area ----
$route['priest']                     = 'priest/dashboard';
$route['priest/(:any)']              = 'priest/$1';
