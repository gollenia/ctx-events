<?php

namespace Contexis\Events\Admin;

use Contexis\Events\Admin\Pagination;
use Contexis\Events\Collections\BookingCollection;
use Contexis\Events\Collections\CouponCollection;
use Contexis\Events\Core\Container;
use Contexis\Events\Core\Request;
use Contexis\Events\Intl\Date;
use Contexis\Events\Models\Booking;
use Contexis\Events\Models\BookingStatus;
use Contexis\Events\Models\Event;
use Contexis\Events\Models\Ticket;
use Contexis\Events\Payment\GatewayCollection;
use Contexis\Events\Core\Utilities\EventScope;

class BookingsTable {

	use \Contexis\Events\Core\Contracts\Http;

	public array $cols = ['user_name','event_name','booking_spaces','booking_status','booking_price','donation'];
	public array $cols_template = [];
	public array $sortable_cols = ['booking_date'];

	public $cols_view;
	
	public array $states = [];
	public int $limit = 20;
	public string $order = 'ASC';
	public string $orderby = 'booking_name';
	public int $page = 1;
	public int $offset = 0;
	public string $scope = 'future';
	public bool $show_tickets = false;
	public ?BookingCollection $bookings;
	public array $status = [0];
	public array $cols_tickets_template = [];
	public ?Ticket $ticket;
	public ?Event $event = null;
	
	
	function __construct(){

		$this->cols_template = apply_filters('em_bookings_table_cols_template', array(
			'user_name'=>__('Name','events'),
			'first_name'=>__('First Name','events'),
			'last_name'=>__('Last Name','events'),
			'event_name'=>__('Event','events'),
			'event_date'=>__('Event Date(s)','events'),
			'event_time'=>__('Event Time(s)','events'),
			'user_email'=>__('E-mail','events'),
			'booking_spaces'=>__('Spaces','events'),
			'booking_status'=>__('Status','events'),
			'booking_date'=>__('Booking Date','events'),
			'booking_price'=>__('Total','events'),
			'booking_id'=>__('Booking ID','events'),
			'booking_comment'=>__('Booking Comment','events'),
			'donation'=>__('Donation','events'),
		), $this);

		$this->cols_tickets_template = apply_filters('em_bookings_table_cols_tickets_template', array(
			'ticket_name'=>__('Ticket Name','events'),
			'ticket_description'=>__('Ticket Description','events'),
			'ticket_price'=>__('Ticket Price','events'),
			'ticket_total'=>__('Ticket Total','events'),
			'ticket_id'=>__('Ticket ID','events')
		), $this);


		if( $this->get_ticket() !== false ){
			$this->cols_view = $this->get_ticket();
		}elseif( $this->event !== null ){
			$this->cols_view = $this->event;
		}

		$this->get_request();

		if( $this->show_tickets ){
			$this->cols = array('user_name','event_name','ticket_name','ticket_price','booking_spaces','booking_status');
			$this->cols_template = array_merge( $this->cols_template, $this->cols_tickets_template);
		}
		
		do_action('em_bookings_table', $this);
	}

	private function get_available_states() : array {
		return [
			[ 'label'=>__('Needs Attention','events'), 'value' => [BookingStatus::PENDING, BookingStatus::AWAITING_ONLINE_PAYMENT] ],
			[ 'label' => __('All Bookings','events'), 'value' => BookingStatus::cases() ],
			[ 'label' => __('Pending','events') , 'value' => [BookingStatus::PENDING] ],
			[ 'label' => __('Confirmed','events'), 'value' => [BookingStatus::APPROVED] ],
			[ 'label' => __('Canceled','events'), 'value' => [BookingStatus::CANCELED] ],
			[ 'label' => __('Rejected','events'), 'value' => [BookingStatus::REJECTED] ],
			[ 'label' => __('Awaiting Online Payment'), 'value' => [BookingStatus::AWAITING_ONLINE_PAYMENT] ],
		];
	}

	private function get_request() 
	{ 
		$this->order = $this->http()->string('order', 'ASC');
		$this->orderby = $this->http()->string('orderby', 'booking_name');
		$this->limit = $this->http()->int('limit', 20);
		$this->page = $this->http()->int('pno', 1);
		$this->scope = $this->http()->string('scope', 'future');
		$this->status = $this->http()->array('status', [0]);
		$this->offset = ( $this->page > 1 ) ? ($this->page-1)*$this->limit : 0;
		$this->event = Event::get_by_id($this->http()->int('eventid', 0));

		$columns = $this->http()->array('cols', []);
		if(empty($_REQUEST['no_save'])) {
			$this->save_user_preferences();
		}


		if(empty($_REQUEST['cols'])) {
			$this->load_user_preferences();
			return;
		}

		$columns = $_REQUEST['cols'];

		if(!is_array($columns)) {
			$columns = explode(',', $columns);
		}

		$this->cols = array();
		foreach( $columns as $column ){
			if( !array_key_exists($column, $this->cols_template) ) continue;
			
			$this->cols[] = sanitize_text_field($column);
		}
		
		
	}


	private function load_user_preferences() {
		$settings_key = 'em_bookings_view';
	
		if (!empty($this->cols_view) && is_object($this->cols_view)) {
			$settings_key .= '-' . get_class($this->cols_view);
		}
	
		$settings = get_user_meta(get_current_user_id(), $settings_key, true);
		
		foreach($settings as $key => $column){
			if( array_key_exists($column, $this->cols_template)){
				$this->cols[$key] = $column;
			}
		}
		return $this->cols;
	}
	
	/**
	 * Speichert die Benutzerpräferenzen für die Tabellenspalten.
	 */
	private function save_user_preferences() {
		$settings_key = 'em_bookings_view';
	
		if (!empty($this->cols_view) && is_object($this->cols_view)) {
			$settings_key .= '-' . get_class($this->cols_view);
		}
	
		update_user_meta(get_current_user_id(), $settings_key, $this->cols);
	}


	/**
	 * @return Ticket|false
	 */
	function get_ticket(){
		if(!isset($_REQUEST['ticket_id'])) return false;
		if(!isset($_REQUEST['eventid'])) return false;
		$ticket_id = is_numeric($_REQUEST['ticket_id']) ? $_REQUEST['ticket_id'] : 0;
		$event_id = is_numeric($_REQUEST['eventid']) ? $_REQUEST['eventid'] : 0;
		$ticket = Ticket::get_by_id($event_id, $ticket_id);
		
		if( !empty($this->ticket) && is_object($this->ticket) ){
			return $this->ticket;
		}elseif( !empty($ticket) && is_object($ticket) ){
			return $ticket;
		}
		return false;
	}
	
	/**
	 * Gets the bookings for this object instance according to its settings
	 * @param boolean $force_refresh
	 */
	private function get_bookings($force_refresh = true) : BookingCollection {
		$args = [
			'limit' => $this->limit,
			'offset' => $this->offset,
			'order' => $this->order,
			'orderby' => $this->orderby,
			'status' => $this->status,
			'scope' => $this->event ? false : $this->scope,
		];

		if( $this->event !== null ) $args['event'] = $this->event->event_id;
		
		$this->bookings = BookingCollection::find($args);
		
		return $this->bookings;
	}
	
	function get_count(){
		return count($this->bookings);
	}
	
	function output(){
		do_action('em_bookings_table_header',$this); //won't be overwritten by JS	
		$this->output_overlays();
		$this->output_table();
		do_action('em_bookings_table_footer',$this); //won't be overwritten by JS	
	}
	
	function output_overlays(){
		$ticket = $this->get_ticket();
		
		?>
		<div id="em-bookings-table-settings" class="em-bookings-table-overlay" style="display:none;" title="<?php esc_attr_e('Bookings Table Settings','events'); ?>">
			<form id="em-bookings-table-settings-form" class="em-bookings-table-form" action="" method="post">
				<p><?php _e('Modify what information is displayed in this booking table.','events') ?></p>
				<div id="em-bookings-table-settings-form-cols">
					<p>
						<strong><?php _e('Columns to show','events')?></strong><br />
						<?php _e('Drag items to or from the left column to add or remove them.','events'); ?>
					</p>
					<ul id="em-bookings-cols-active" class="em-bookings-cols-sortable">
						<?php foreach( $this->cols as $col_key ): ?>
							<li class="ui-state-highlight">
								<input id="em-bookings-col-<?php echo esc_attr($col_key); ?>" type="hidden" name="<?php echo esc_attr($col_key); ?>" value="1" class="em-bookings-col-item" />
								<?php echo esc_html($this->cols_template[$col_key]); ?>
							</li>
						<?php endforeach; ?>
					</ul>			
					<ul id="em-bookings-cols-inactive" class="em-bookings-cols-sortable">
						<?php foreach( $this->cols_template as $col_key => $col_data ): ?>
							<?php if( !in_array($col_key, $this->cols) ): ?>
								<li class="ui-state-default">
									<input id="em-bookings-col-<?php echo esc_attr($col_key); ?>" type="hidden" name="<?php echo esc_attr($col_key); ?>" value="0" class="em-bookings-col-item"  />
									<?php echo esc_html($col_data); ?>
								</li>
							<?php endif; ?>
						<?php endforeach; ?>
					</ul>
				</div>
			</form>
		</div>
		<?php if ( $this->event ) : ?>
		<div id="em-bookings-table-export" class="em-bookings-table-overlay" style="display:none;" title="<?php esc_attr_e('Export Bookings','events'); ?>">
			<form id="em-bookings-table-export-form" class="em-bookings-table-form" action="<?php echo admin_url('admin-ajax.php') ?>" method="post">
				<p><?php _e('Select the options below and export all the bookings you have currently filtered (all pages) into a CSV spreadsheet format.','events') ?></p>
				
				<p>
				<input type="checkbox" name="show_tickets" value="1" />
				<label><?php _e('Split bookings by ticket type','events')?> </label>
				
				<?php do_action('em_bookings_table_export_options'); ?>
				<div id="em-bookings-table-settings-form-cols">
					<p><strong><?php _e('Columns to export','events')?></strong></p>
					<ul id="em-bookings-export-cols-active" class="em-bookings-cols-sortable">
						<?php foreach( $this->cols as $col_key ): ?>
							<li class="ui-state-highlight">
								<input id="em-bookings-col-<?php echo esc_attr($col_key); ?>" type="hidden" name="cols[<?php echo esc_attr($col_key); ?>]" value="1" class="em-bookings-col-item" />
								<?php echo esc_html($this->cols_template[$col_key]); ?>
							</li>
						<?php endforeach; ?>
					</ul>			
					<ul id="em-bookings-export-cols-inactive" class="em-bookings-cols-sortable">
						<?php foreach( $this->cols_template as $col_key => $col_data ): ?>
							<?php if( !in_array($col_key, $this->cols) ): ?>
								<li class="ui-state-default">
									<input id="em-bookings-col-<?php echo esc_attr($col_key); ?>" type="hidden" name="cols[<?php echo esc_attr($col_key); ?>]" value="0" class="em-bookings-col-item"  />
									<?php echo esc_html($col_data); ?>
								</li>
							<?php endif; ?>
						<?php endforeach; ?>
						<?php if( !$this->show_tickets ): ?>
						<?php foreach( $this->cols_tickets_template as $col_key => $col_data ): ?>
							<?php if( !in_array($col_key, $this->cols) ): ?>
								<li class="ui-state-default <?php if(array_key_exists($col_key, $this->cols_tickets_template)) echo 'em-bookings-col-item-ticket'; ?>">
									<input id="em-bookings-col-<?php echo esc_attr($col_key); ?>" type="hidden" name="cols[<?php echo esc_attr($col_key); ?>]" value="0" class="em-bookings-col-item"  />
									<?php echo esc_html($col_data); ?>
								</li>
							<?php endif; ?>
						<?php endforeach; ?>
						<?php endif; ?>
					</ul>
				</div>
				<?php if( $this->event ): ?>
				<input type="hidden" name="eventid" value='<?php echo esc_attr($this->event->event_id); ?>' />
				<?php endif; ?>
				<?php if( $ticket !== false ): ?>
				<input type="hidden" name="ticket_id" value='<?php echo esc_attr($ticket->ticket_id); ?>' />
				<?php endif; ?>
				<input type="hidden" name="scope" value='<?php echo esc_attr($this->scope); ?>' />
				<input type="hidden" name="status" value='<?php echo esc_attr($this->status); ?>' />
				<input type="hidden" name="no_save" value='1' />
				<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('em_export_bookings_xls'); ?>" />
				<input type="hidden" name="action" value="em_export_bookings_xls" />
			</form>
		</div>
		<?php endif; ?>
		<br class="clear" />
		<?php
	}
	
	function output_table(){
		$this->get_bookings(true); //get bookings and refresh
		?>
		<div class='em-bookings-table em_obj' id="em-bookings-table">
			<form class='bookings-filter' method='get' action='<?php echo esc_url(site_url()); ?>/wp-admin/edit.php'>
				<?php if( $this->event ): ?>
				<input type="hidden" name="eventid" value='<?php echo esc_attr($this->event->event_id); ?>' />
				<?php endif; ?>
				
				<input type="hidden" name="is_public" value="<?php echo ( !empty($_REQUEST['is_public']) || !is_admin() ) ? 1:0; ?>" />
				<input type="hidden" name="pno" value='<?php echo esc_attr($this->page); ?>' />
				<input type="hidden" name="order" value='<?php echo esc_attr($this->order); ?>' />
				<input type="hidden" name="orderby" value='<?php echo esc_attr($this->orderby); ?>' />
				<input type="hidden" name="post_type" value="event" />
				<input type="hidden" name="page" value="events-bookings" />
				<input type="hidden" name="cols" value="<?php echo esc_attr(implode(',', $this->cols)); ?>" />
				
				<div class='tablenav'>
					<div class="alignleft actions">
						<?php if( $this->event ): ?>
						<a href="#" class="em-bookings-table-export button-secondary" id="em-bookings-table-export-trigger" rel="#em-bookings-table-export" title="<?php _e('Export these bookings.','events'); ?>"><i class="material-symbols-outlined">export_notes</i></a>
						<?php endif; ?>
						<a href="#" class="em-bookings-table-settings button-secondary" id="em-bookings-table-settings-trigger" rel="#em-bookings-table-settings"><i class="material-symbols-outlined">table</i></a>
						<?php if( $this->event === null ): ?>
						<select name="scope">
							<?php
							foreach ( EventScope::get_all() as $key => $value ) {
								$selected = "";
								if ($key == $this->scope)
									$selected = "selected='selected'";
								echo "<option value='".esc_attr($key)."' $selected>".esc_html($value)."</option>  ";
							}
							?>
						</select>
						<?php endif; ?>
						<select name="limit">
							<option value="<?php echo esc_attr($this->limit) ?>"><?php echo esc_html(sprintf(__('%s Rows','events'),$this->limit)); ?></option>
							<option value="5">5</option>
							<option value="10">10</option>
							<option value="25">25</option>
							<option value="50">50</option>
							<option value="100">100</option>
						</select>
						<select name="status">
							<?php foreach ( $this->get_available_states() as $state ) {
								$selected = in_array($this->status, $state['value'], true) ? "selected='selected'" : '';
								echo "<option value='" . join(',', array_map(fn($s) => $s->value, $state['value'])) . "' $selected>"
									. $state['label']
									. "</option>";
							} ?>
						</select>
						
						<input name="pno" type="hidden" value="1" />
						<button id="post-query-submit" class="button-secondary" type="submit" value="" ><?php esc_attr_e( 'Filter' )?>
						
					</div>
					<?php 
					if ( count($this->bookings) >= $this->limit ) {
						$bookings_nav = Pagination::paginate( count($this->bookings), $this->limit, $this->page, array(),'#%#%','#');
						echo $bookings_nav;
					}
					?>
				</div>
				<div class="clear"></div>
				<div class='table-wrap'>
				<table id='dbem-bookings-table' class='widefat post bookingstable'>
					<thead>
						<tr>
							<?php /*						
							<th class='manage-column column-cb check-column' scope='col'>
								<input class='select-all' type="checkbox" value='1' />
							</th>
							*/ ?>
							<th class='manage-column' scope='col'><?php echo implode("</th><th class='manage-column' scope='col'>", $this->get_headers()); ?></th>
						</tr>
					</thead>
					<?php if( count($this->bookings) > 0 ): ?>
					<tbody>
						<?php 
						
						$event_count = (!empty($event_count)) ? $event_count:0;
						foreach ($this->bookings as $booking) {
							var_dump($booking);
							?>
							<tr>
								<?php 
								
								if( $this->show_tickets ){
									$attendees = $booking->attendees ?? [];
									foreach ( $attendees as $ticket_id => $entries ) {
										$ticket = \Contexis\Events\Models\Ticket::get_by_id($booking->event_id, $ticket_id);
								
										$ticket_name = $ticket ? $ticket->ticket_name : __('Unknown Ticket', 'events');
										$count = count($entries);
								
										?>
										<tr>
											<td><?php echo esc_html( $ticket_name ); ?></td>
											<td><?php echo esc_html( $count ); ?></td>
											<!-- Du kannst hier natürlich noch mehr anzeigen -->
										</tr>
										<?php
									}
								} else {
									$row = $this->get_row($booking);
									foreach( $row as $row_cell ){
									?><td class="<?php echo $row_cell['class']; ?>"><?php echo $row_cell['content']; ?></td><?php
									}
								}
								?>
							</tr>
							<?php
						}
						?>
					</tbody>
					<?php else: ?>
						<tbody>
							<tr><td scope="row" colspan="<?php echo count($this->cols); ?>"><?php _e('No bookings.', 'events'); ?></td></tr>
						</tbody>
					<?php endif; ?>
				</table>
				</div>
				<?php if( !empty($bookings_nav) && count($this->bookings) >= $this->limit ) : ?>
				<div class='tablenav'>
					<?php echo $bookings_nav; ?>
					<div class="clear"></div>
				</div>
				<?php endif; ?>
			</form>
		</div>
		<?php
	}
	
	function get_headers($csv = false){
		$headers = array();
		foreach($this->cols as $col){
			if( $col == 'actions' ){
				if( !$csv ) $headers[$col] = '&nbsp;';
			}elseif(array_key_exists($col, $this->cols_template)){
				/* for later - col ordering!
				if($this->orderby == $col){
					if($this->order == 'ASC'){
						$headers[] = '<a class="em-bookings-orderby" href="#'.$col.'">'.$this->cols_template[$col].' (^)</a>';
					}else{
						$headers[] = '<a class="em-bookings-orderby" href="#'.$col.'">'.$this->cols_template[$col].' (d)</a>';
					}
				}else{
					$headers[] = '<a class="em-bookings-orderby" href="#'.$col.'">'.$this->cols_template[$col].'</a>';
				}
				*/
				$v = $this->cols_template[$col];
				if( $csv ){
					$v = self::sanitize_spreadsheet_cell($v);
				}
				$headers[$col] = $csv ? '<b>' . $v . '</b>' : $v;
			}
		}
		return apply_filters('em_bookings_table_get_headers', $headers, $csv, $this);
	}
	
	function get_table(){
		
	}
	
	/**
	 * @param Object $object
	 * @return array()
	 */
	function get_row( $object, $format = 'html' ){
		
		$booking = $object;
		
		$cols = array();
		foreach($this->cols as $col){
			if( $col == 'actions' && $format == 'csv' ) continue; 
			$cols[] = ['content' => $this->get_cell($booking, $col, $format), 'class' => 'em-bookings-col-'.$col];
		}
		return $cols;
	}

	function get_cell($booking, $column, $format = 'html'){
		$ticket_id = array_keys($booking->attendees)[0];
		$ticket = Ticket::get_by_id($booking->event_id, $ticket_id);
		$price_array = $booking->get_price_summary_array();
		switch ($column) {
			case 'user_email':
				return $booking->booking_mail;
				break;
			case 'user_name':
				if( $format == 'csv' ) return $booking->get_full_name;
				$url = $booking->get_event()->get_bookings_url();
				$url = add_query_arg(['booking_id'=>$booking->id, 'em_ajax'=>null, 'em_obj'=>null, 'booking_email'=>$booking->user_email], $url);
				$ret = "<strong><a class='row-title' href='$url'>" . $booking->get_full_name() . '</a></strong>';
				$ret .= "<div class='row-actions'>" . implode(' | ', $this->get_booking_actions($booking)) . "</div>";
				return $ret;
				break;
			case 'first_name':
				return $booking->get_first_name();
				break;
			case 'last_name':
				return $booking->get_last_name();
				break;
			case 'event_name':
				return $format == 'csv' ? $booking->get_event()->event_name : '<a href="'.$booking->get_event()->get_bookings_url().'">'. esc_html($booking->get_event()->event_name) .'</a>';
				break;
			case 'event_date':
				return $booking->get_event()->render('#_EVENTDATES');
				break;
			case 'event_time':
				return $booking->get_event()->render('#_EVENTTIMES');
				break;
			case 'booking_price':
				return \Contexis\Events\Intl\Price::format( $price_array['total'] );
				break;
			case 'donation':
				return \Contexis\Events\Intl\Price::format( $price_array['donation'] );
				break;
			case 'booking_status':
				if( $format == 'csv' ) return $booking->get_status();
				//$status = array_search($booking->booking_status, array_column($this->states, 'search'));
				return '<span class="em-label"><i class="material-symbols-outlined">'.$booking->get_status_icon().'</i>'.ucwords($booking->get_status()).'</span>';
				break;
			case 'booking_date':
				return $booking->booking_date ? Date::get_date($booking->booking_date->getTimestamp()) : 'NODATE';
				break;
			case 'booking_id':
				return $booking->id;
				break;
			case 'actions':
				return '';
				break;
			case 'booking_spaces':
				return $booking->get_booked_spaces();
				break;
			case 'booking_comment':
				return $booking->booking_comment;
				break;
			case 'ticket_name':
				return $ticket?->ticket_name ?? __('Unknown Ticket', 'events');
				break;
			case 'ticket_description':
				return $ticket?->ticket_description ?? __('No description available', 'events');
				break;
			case 'ticket_price':
				return \Contexis\Events\Intl\Price::format( $booking->get_price() );
				break;
			case 'ticket_total':
				return $ticket->ticket_price;
				break;
			case 'ticket_id':
				return $ticket->ticket_id;
				break;
			case 'dbem_phone':
				return $booking->registration['phone'] ?? '';
				break;
			case 'coupons':
				return implode(', ', $booking->get_coupons());
				if( !CouponCollection::booking_has_coupons($booking) ) {
					return '';
					break;
				}
				$coupon_codes = array();
				$coupons = CouponCollection::booking_get_coupons($booking);
				foreach( $coupons as $EM_Coupon ){
					$coupon_codes[] = $EM_Coupon->code;
				}
				$coupon_codes = implode(' ', $coupon_codes);
				
				return $coupon_codes;
				break;
			case 'gateway':
				if( !empty($booking->gateway) ){
					$gateway = GatewayCollection::all()->get($booking->gateway);
					$value = $gateway->title;
				}else{
					$value = __('None','events');
				}
				return $value;
				break;
			default:
				return apply_filters('em_bookings_table_rows_col', $column, $booking, $format);
				break;
		}
	}

	function get_status_icon ($status) {
		$icons = [
			'pending',
			'check_circle',
			'check_circle',
			'block',
			'pan_tool',
			'overview',
			'overview',
			'credit_card_clock',
			'overview',
		];
		return $icons[$status];
	}
	
	function get_row_csv($booking){
	    $row = $this->get_row($booking, 'csv');
	    foreach($row as $k=>$v){
	    	$row[$k] = html_entity_decode($v['content']);
	    } 
	    return $row;
	}
	
	public static function sanitize_spreadsheet_cell( $cell ){
		return preg_replace('/^([;=@\+\-])/', "'$1", $cell);
	}
	

	function get_booking_actions(Booking $booking){
		$booking_actions = array();

		switch($booking->status){
			case BookingStatus::PENDING: 
				if( !get_option('dbem_bookings_approval') ) break;
				$actions = ['approve', 'reject'];
				break;

			case BookingStatus::APPROVED:
				$actions = ['unapprove', 'cancel'];
				break;
			default:
				$actions = ['approve'];
				break;	
		}

		$actions = apply_filters('em_bookings_table_booking_actions_'.$booking->status->value, $actions, $booking);
		$actions[] = 'delete';
		$booking_actions = $this->generate_action_links($actions, $booking);
		
		return apply_filters('em_bookings_table_cols_col_action', $booking_actions, $booking);
	}

	private function generate_action_links(array $actions, $booking) : array {
		$links = [];

		foreach($actions as $action) {
			$class = $action== 'delete' ? 'trash' : '';
			$links[] =  "<span class='$class'><a class='em-bookings-action' data-action='$action' data-booking-id='$booking->id'>" . __(ucfirst($action), 'events') . "</a></span>";
		}
		
		return $links;
	}
}
?>