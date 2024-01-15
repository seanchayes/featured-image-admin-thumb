# Featured Image Admin Thumb

Featured Image Admin Thumbnails plugin enabled in the All Posts/All Pages views
to display thumbnail images next to each post / page if one is present

Adds inline thumbnail image to admin columns for All Post/post types view (where supported). Click to easily set/change the featured image for that content.

## Description

When active this plugin adds a column to the All Posts/All Pages and where supported the All Custom Post Type admin views. This column, "Thumb", will display a thumbnail of the featured image
 or post thumbnail so you can easily determine the content that is missing an image.

You can also make inline edits that allow you to change the thumb/image from the All Posts/All Pages/All Custom Post Type view without having to edit the post.

### Installation

#### Using The WordPress Dashboard

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'featured-image-admin-thumb'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

#### Uploading in WordPress Dashboard

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `featured-image-admin-thumb.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard
6. Consider using a thumbnail regenerator to create specific thumbnails for the All Posts/Pages admin views

#### Using FTP

1. Download `featured-image-admin-thumb.zip`
2. Extract the `featured-image-admin-thumb` directory to your computer
3. Upload the `featured-image-admin-thumb` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard


## Frequently Asked Questions

### You don't see the thumb column and the thumbnails in that column

After checking that the plugin is enabled, be sure to click Screen Options if you don't immediately see the column and ensure "Thumb" has a checkmark next to it

### Screenshots

1. This shows the Thumb column in the All Pages/Posts admin view and in this case a thumbnail is available and shown. There would be a blank space if no thumbnail was found for the post or page.
2. If the column is not showing in your All Posts/Pages view this shows you where you can check to see that the "Thumb" column is selected to be displayed

### Changelog
== Changelog ==

#### 1.5.2 (2018-09-26) ####
* Addresses an issue where in Excerpt view where the displayed thumbnail did not represent the thumbnail in the post. Switching to List view, however, the thumbnail showed correctly.
* Fix for support request [odd thumb behavior in excerpt view:](https://wordpress.org/support/topic/odd-thumb-behavior-in-excerpt-view/)

#### 1.5.1 (2018-08-31) ####
* Add thumb column to EDD Downloads. Use filter (documented in 1.4.2 release) if you want to exclude thumb column in EDD Downloads. Code linting.

#### 1.5.0 (2018-06-21) ####
* Ability to sort thumbnail column. You can easily see the content that has a thumbnail, displays no thumbnail or has no thumbnail assigned to it.

#### 1.4.4 (2017-11-22) ####
* Returning an accidental PHP specific array definition to be compatible with earlier PHP versions

##### 1.4.3 (2017-11-10) #####
* A general tidy up release
* Code tidy up, removal of unneeded definitions and legacy unused functions
* Correcting return variables
* Adjust `fiat_thumb` image size to be square and have a centered crop
**you might** need to regenerate thumbnails if there are visual inconsistencies with thumnbails.
* Escaping in markup has been improved

##### 1.4.2 (2017-5-15) #####
* Updated list of default excluded post types
* Page post type is now included by default hopefully fixing [this support query:](https://wordpress.org/support/topic/4-7-changed-the-flow-odd-use/)
* Added filter "fiat/restrict_post_types"
* Example filter usage `add_filter( 'fiat/restrict_post_types', 'your_theme_name_restrict_post_types' );` then create a function `your_theme_name_restrict_post_types` to modify and return and array of post_types to exclude

##### 1.4.1 (2016-6-9) #####
* Bug fix when checking sizes and removal of pencil based on support feedback

##### 1.4 (2015-11-16) #####
* Language translation functionality added 

##### 1.3.6 (2015-10-29) #####
* Removed post check inadvertently included from another branch and was restricting display of thumbnail column

##### 1.3.5 (2015-10-28) #####
* Check if WooCommerce is installed and if so adjust column output on WooCommerce product post type page [reported here thank you:](https://wordpress.org/support/topic/conflict-with-woocommerce-25)

##### 1.3.4 (2015-08-14) #####
* Check if sizes is an array before looping over it as [reported here:](https://wordpress.org/support/topic/how-to-cure-sql-corruption) to prevent PHP error display
* Updated stable tag

##### 1.3.3 (2015-02-04) #####
* Additional excluded post type
* Markup and JS to support showing the selected thumbnail in the media uploader

##### 1.3.2 (2015-02-04) #####
* Minor improvement to code that attaches FIAT column handler to Posts, Pages and other taxonomy screens
* Inclusion of fix from [here:](https://wordpress.org/support/topic/would-like-to-see-media-category-sort) that allows filters from other plugins to work

##### 1.3.1 (2014-07-24) #####
* Adding in missing Genericons.

##### 1.3 (2014-07-15) #####
* Added highlighting on images in All Post/All Pages/All Custom Post Type screens - displaying an edit icon that when clicked can change the featured image inline without having to edit the relevant content item

##### 1.2 #####
* Add custom media uploader (css and js) support for all registered and active custom post types

##### 1.1 #####
* Improved sizing logic when displaying existing thumbnails

##### 1.0.2 #####
* Updated logic to detect best thumbnail size to use

##### 1.0.1 #####
* Added thumbnail size check to use default "thumbnail" if bundled "fiat_thumb" is not available for some images

##### 1.0 #####
* First version

== Upgrade Notice ==

== Updates ==
