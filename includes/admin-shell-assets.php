<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'admin_enqueue_scripts',
	static function () {
		if ( ! isset( $_GET['page'] ) || sanitize_key( wp_unslash( (string) $_GET['page'] ) ) !== pw_admin_page_slug() ) {
			return;
		}

		wp_enqueue_style(
			'pw-admin-fonts',
			'https://fonts.googleapis.com/css2?family=Inter+Tight:wght@300;400;500;600;700&display=swap',
			array(),
			null
		);

		$css = "
.pw-admin{
  --bg:#F5F3EE;--surface:#FFFFFF;--card:#FAFAF8;--card2:#F0EDE6;
  --border:rgba(0,0,0,0.09);--border2:rgba(0,0,0,0.18);
  --text:#1A1917;--sub:#504D48;--muted:#7F7C77;
  --primary:#C92A08;--primaryDark:#A32206;
  font-family:'Inter Tight',system-ui,sans-serif;
}
.pw-admin .pw-header{display:flex;align-items:center;justify-content:space-between;gap:14px;margin:6px 0 10px}
.pw-admin .pw-brand{display:flex;align-items:center;gap:10px;min-width:0}
.pw-admin .pw-logo{width:26px;height:26px;display:block}
.pw-admin .pw-brand-text{display:flex;align-items:center;gap:10px;min-width:0;flex-wrap:wrap}
.pw-admin .pw-title{font-size:20px;font-weight:650;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.15}
.pw-admin .pw-version{font-size:12px;font-weight:700;color:var(--muted);background:rgba(0,0,0,0.04);border:1px solid var(--border);padding:2px 8px;border-radius:999px}
.pw-admin .pw-tabs{display:flex;gap:6px;border-bottom:1px solid var(--border);margin:10px 0 16px;overflow-x:auto;scrollbar-width:none;-ms-overflow-style:none}
.pw-admin .pw-tabs::-webkit-scrollbar{display:none}
.pw-admin .pw-tab{display:inline-flex;align-items:center;padding:10px 12px;font-size:13px;font-weight:600;color:var(--muted);text-decoration:none;border-bottom:2px solid transparent;margin-bottom:-1px}
.pw-admin .pw-tab:hover{color:var(--text)}
.pw-admin .pw-tab.is-active{color:var(--text);border-bottom-color:var(--primary)}
.pw-admin .pw-card{background:var(--card);border:1px solid var(--border);border-radius:10px;overflow:hidden;max-width:980px}
.pw-admin .pw-card + .pw-card{margin-top:22px}
.pw-admin .pw-card-head{background:var(--card2);border-bottom:1px solid var(--border);padding:12px 18px;display:flex;align-items:center;justify-content:space-between}
.pw-admin .pw-card-title{font-size:12px;font-weight:600;letter-spacing:0.02em;color:var(--sub)}
.pw-admin .pw-card-body{padding:16px}
.pw-admin .pw-footer{max-width:980px;margin-top:12px;color:var(--muted);font-size:12px}
.pw-admin .pw-footer-link{color:var(--muted);text-decoration:none}
.pw-admin .pw-footer-link:hover{color:var(--text);text-decoration:underline}
@media (max-width:960px){
  .pw-admin .pw-header{flex-direction:column;align-items:flex-start}
  .pw-admin .pw-card{max-width:none}
}
";

		wp_register_style( 'pw-admin', false, array( 'pw-admin-fonts' ), '0.9.0' );
		wp_enqueue_style( 'pw-admin' );
		wp_add_inline_style( 'pw-admin', $css );
	}
);
