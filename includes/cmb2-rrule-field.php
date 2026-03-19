<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'cmb2_render_pw_rrule', 'pw_cmb2_render_rrule_field', 10, 5 );
add_filter( 'cmb2_sanitize_pw_rrule', 'pw_cmb2_sanitize_rrule_field', 10, 2 );

function pw_cmb2_render_rrule_field( $field, $value, $object_id, $object_type, $field_type ) {
	$value = $value ?: '';

	$freq     = 'WEEKLY';
	$interval = 1;
	$byday    = [];
	$until    = '';

	if ( $value ) {
		foreach ( explode( ';', $value ) as $part ) {
			$pair = array_pad( explode( '=', $part ), 2, '' );
			$k    = $pair[0];
			$v    = $pair[1];
			switch ( $k ) {
				case 'FREQ':
					$freq = $v;
					break;
				case 'INTERVAL':
					$interval = (int) $v;
					break;
				case 'BYDAY':
					$byday = explode( ',', $v );
					break;
				case 'UNTIL':
					$until_raw = substr( $v, 0, 8 );
					if ( strlen( $until_raw ) === 8 ) {
						$until = substr( $until_raw, 0, 4 ) . '-' . substr( $until_raw, 4, 2 ) . '-' . substr( $until_raw, 6, 2 );
					}
					break;
			}
		}
	}

	$days_map = [ 'MO' => 'Mon', 'TU' => 'Tue', 'WE' => 'Wed', 'TH' => 'Thu', 'FR' => 'Fri', 'SA' => 'Sat', 'SU' => 'Sun' ];
	$id       = $field->args['id'];
	?>
	<div class="pw-rrule-field" data-field-id="<?php echo esc_attr( $id ); ?>">

		<p>
			<label>Frequency</label><br>
			<select class="pw-rrule-freq">
				<?php foreach ( [ 'DAILY' => 'Daily', 'WEEKLY' => 'Weekly', 'MONTHLY' => 'Monthly', 'YEARLY' => 'Yearly' ] as $val => $label ) : ?>
					<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $freq, $val ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>

		<p>
			<label>Every</label><br>
			<input type="number" class="pw-rrule-interval" value="<?php echo esc_attr( $interval ); ?>" min="1" style="width:60px;">
			<span class="pw-rrule-interval-label">week(s)</span>
		</p>

		<p class="pw-rrule-days" style="<?php echo $freq !== 'WEEKLY' ? 'display:none' : ''; ?>">
			<label>On days</label><br>
			<?php foreach ( $days_map as $code => $label ) : ?>
				<label style="margin-right:8px;">
					<input type="checkbox" class="pw-rrule-day" value="<?php echo esc_attr( $code ); ?>"
						<?php checked( in_array( $code, $byday, true ) ); ?>>
					<?php echo esc_html( $label ); ?>
				</label>
			<?php endforeach; ?>
		</p>

		<p>
			<label>End date (optional)</label><br>
			<input type="date" class="pw-rrule-until" value="<?php echo esc_attr( $until ); ?>">
		</p>

		<p>
			<label>Generated RRULE</label><br>
			<input type="text" class="pw-rrule-display" readonly style="width:100%;font-family:monospace;" value="<?php echo esc_attr( $value ); ?>">
		</p>

		<?php echo $field_type->input( [ 'type' => 'hidden', 'class' => 'pw-rrule-value', 'value' => $value ] ); ?>
	</div>

	<script>
	(function() {
		document.querySelectorAll('.pw-rrule-field[data-field-id="<?php echo esc_js( $id ); ?>"]').forEach(function(wrap) {
			function build() {
				var freq     = wrap.querySelector('.pw-rrule-freq').value;
				var interval = parseInt(wrap.querySelector('.pw-rrule-interval').value, 10) || 1;
				var until    = wrap.querySelector('.pw-rrule-until').value;
				var days     = Array.from(wrap.querySelectorAll('.pw-rrule-day:checked')).map(function(c) { return c.value; });

				var labels = { DAILY: 'day(s)', WEEKLY: 'week(s)', MONTHLY: 'month(s)', YEARLY: 'year(s)' };
				wrap.querySelector('.pw-rrule-interval-label').textContent = labels[freq] || 'unit(s)';
				wrap.querySelector('.pw-rrule-days').style.display = freq === 'WEEKLY' ? '' : 'none';

				var rule = 'FREQ=' + freq;
				if (interval > 1) rule += ';INTERVAL=' + interval;
				if (freq === 'WEEKLY' && days.length) rule += ';BYDAY=' + days.join(',');
				if (until) rule += ';UNTIL=' + until.replace(/-/g,'') + 'T000000Z';

				wrap.querySelector('.pw-rrule-display').value = rule;
				wrap.querySelector('.pw-rrule-value').value   = rule;
			}

			wrap.querySelectorAll('select, input').forEach(function(el) {
				el.addEventListener('change', build);
				el.addEventListener('input',  build);
			});

			build();
		});
	})();
	</script>
	<?php
}

function pw_cmb2_sanitize_rrule_field( $override, $value ) {
	return preg_replace( '/[^A-Z0-9=;,TZ]/', '', strtoupper( (string) $value ) );
}
