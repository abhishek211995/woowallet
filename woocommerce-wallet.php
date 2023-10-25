<?php
/**
 * Woocommerce Wallet
 *
 * @package       WOOCOMMERC
 * @author        Abhishek Potdar
 * @license       gplv2
 * @version       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:   Woocommerce Wallet
 * Plugin URI:    https://purecss.co.in/
 * Description:   Enable the power of wallet on your Woocommerce store easily
 * Version:       1.0.0
 * Author:        Abhishek Potdar
 * Author URI:    https://purecss.co.in/
 * Text Domain:   woocommerce-wallet
 * Domain Path:   /languages
 * License:       GPLv2
 * License URI:   https://www.gnu.org/licenses/gpl-2.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with Woocommerce Wallet. If not, see <https://www.gnu.org/licenses/gpl-2.0.html/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
// Plugin name
define( 'WOOCOMMERC_NAME',			'Woocommerce Wallet' );

// Plugin version
define( 'WOOCOMMERC_VERSION',		'1.0.0' );

// Plugin Root File
define( 'WOOCOMMERC_PLUGIN_FILE',	__FILE__ );

// Plugin base
define( 'WOOCOMMERC_PLUGIN_BASE',	plugin_basename( WOOCOMMERC_PLUGIN_FILE ) );

// Plugin Folder Path
define( 'WOOCOMMERC_PLUGIN_DIR',	plugin_dir_path( WOOCOMMERC_PLUGIN_FILE ) );

// Plugin Folder URL
define( 'WOOCOMMERC_PLUGIN_URL',	plugin_dir_url( WOOCOMMERC_PLUGIN_FILE ) );

/**
 * Load the main class for the core functionality
 */
require_once WOOCOMMERC_PLUGIN_DIR . 'core/class-woocommerce-wallet.php';

/**
 * The main function to load the only instance
 * of our master class.
 *
 * @author  Abhishek Potdar
 * @since   1.0.0
 * @return  object|Woocommerce_Wallet
 */
function WOOCOMMERC() {
	return Woocommerce_Wallet::instance();
}

WOOCOMMERC();
