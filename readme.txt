=== User Avatar for Woo ===
Contributors:       nmtnguyen56
Tags:               woocommerce, avatar, user avatar, customer avatar
Stable tag:         1.0
Requires at least:  5.2
Tested up to:       7.0
Requires PHP:       7.4
Requires Plugins:   woocommerce
License:            GPLv2 or later
License URI:        https://www.gnu.org/licenses/gpl-2.0.html

A simple, secure and lightweight plugin that empowers your Woo users to upload and delete their own profile picture directly on the "My Account" page.

== Description ==

Instead of relying on the default Gravatar, this plugin seamlessly integrates with the WooCommerce "My Account" page, users can upload a custom image, see a preview and even remove it. This enhances user engagement and gives your store a more professional, community-focused feel.

**Key Features:**

* **Seamless Integration:** Automatically adds an avatar management section to the WooCommerce "My Account" details page. No configuration needed.
* **Simple Upload & Preview:** Features a custom-styled "Select photo" button and shows a preview of the current avatar.
* **Avatar Removal:** Users can easily remove their custom avatar with a single click, which reverts them to their default Gravatar.
* **Automatic Cleanup:** When a user uploads a new avatar, the old one is automatically and permanently deleted from your Media Library to save space. When they explicitly remove an avatar, the file is also deleted.
* **Secure & Lightweight:** Built with security best practices, including nonce verification, file type validation, and size checks. No extra libraries or bloat.
* **Translation Ready:** All user-facing strings are internationalized and ready for translation.

== Installation ==

= Automatic Installation (Recommended) =

1.  Navigate to **Plugins > Add New** in your WordPress dashboard.
2.  Search for "User Avatar for Woo".
3.  Click **Install Now**, then **Activate**.
4.  That's it! The avatar field will now appear on the WooCommerce "My Account" -> "Account details" page.

= Manual Installation =

1.  Download the plugin .zip file.
2.  Navigate to **Plugins > Add New** in your WordPress dashboard.
3.  Click **Upload Plugin** and choose the downloaded .zip file.
4.  Activate the plugin through the 'Plugins' menu in WordPress.

OR

1.  Unzip the downloaded plugin file.
2.  Upload the entire `user-avatar-for-woo` folder to the `/wp-content/plugins/` directory on your server.
3.  Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= How does this plugin work? =

The plugin automatically hooks into the WooCommerce "My Account" -> "Account details" page and adds the necessary fields for uploading and managing a profile picture. There are no settings pages to configure.

= Can I customize the appearance of the buttons? =

Yes. The plugin adds specific CSS classes that you can use to style the elements. The main classes are:
* `.btn-upload-avatar`: The custom "Select photo from computer" button.
* `.remove-avatar-label`: The label for the "Delete current profile picture" checkbox.

= What happens to the old avatar when a new one is uploaded? =

To keep your Media Library clean, the old avatar file is **permanently deleted** from your server when a new one is successfully uploaded.

= Is the plugin secure? =

Yes. All actions are verified with WordPress nonces to prevent CSRF attacks. File uploads are validated by size and MIME type (only JPG, PNG, GIF are allowed) on the server-side, and the plugin uses WordPress's core functions for secure file handling.

== Changelog ==

= 1.0 =
Initial release. Basic functionality to upload a custom avatar on the WooCommerce My Account page.

== Upgrade Notice ==

= 1.0 =
This is the first version of the plugin. Enjoy the custom avatar feature!