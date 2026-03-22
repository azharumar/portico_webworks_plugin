# -*- coding: utf-8 -*-
"""One-off generator for gb-pro-markup-samples.html — run from repo root: python tools/generate-gb-fact-sheet-markup.py"""
from pathlib import Path
import json

OUT = Path(__file__).resolve().parent.parent / "gb-pro-markup-samples.html"

# Bracket-free token: GB/WP may strip [shortcodes] in block HTML; replaced in pw_replace_property_currency_token().
CUR = " __PW_PROPERTY_CURRENCY__"

SHELL = {
    "maxWidth": "960px",
    "marginLeft": "auto",
    "marginRight": "auto",
    "paddingTop": "24px",
    "paddingRight": "20px",
    "paddingBottom": "40px",
    "paddingLeft": "20px",
}
SHELL_CSS = ".gb-element-shell{margin-left:auto;margin-right:auto;max-width:960px;padding:24px 20px 40px}"

SEC = {"marginTop": "44px", "paddingTop": "24px"}
SEC_CSS = ".gb-element-sec{margin-top:44px;padding-top:24px}"


def j(obj):
    return json.dumps(obj, separators=(",", ":"))


def text_block(uid, tag, inner_html, mb="0px", extra=None):
    st = {"marginBottom": mb}
    css = f".gb-text-{uid}{{margin-bottom:{mb}}}"
    if extra:
        st.update({k: v for k, v in extra.items() if k != "wordBreak"})
        parts = [f"margin-bottom:{mb}"]
        if "fontSize" in extra:
            parts.append(f"font-size:{extra['fontSize']}")
        if "fontWeight" in extra:
            parts.append(f"font-weight:{extra['fontWeight']}")
        if extra.get("wordBreak"):
            parts.append(f"word-break:{extra['wordBreak']}")
        css = f".gb-text-{uid}{{{';'.join(parts)}}}"
    cn_attr = f"gb-t-{uid}"
    html_class = f"gb-text gb-text-{uid} {cn_attr}"
    return (
        f'<!-- wp:generateblocks/text {{"uniqueId":"{uid}","tagName":"{tag}",'
        f'"styles":{j(st)},"css":"{css}","className":{json.dumps(cn_attr)}}} -->\n'
        f'<{tag} class="{html_class}">{inner_html}</{tag}>\n'
        f"<!-- /wp:generateblocks/text -->\n"
    )


def _dom_attr_str(html_attributes):
    if not html_attributes:
        return ""
    out = []
    for k, v in html_attributes.items():
        name = "colspan" if k == "colSpan" else ("rowspan" if k == "rowSpan" else k)
        out.append(f'{name}="{v}"')
    return " " + " ".join(out)


def element_block(uid, tag, styles, css, inner, html_attributes=None):
    cn_attr = f"gb-el gb-el-{uid}"
    html_class = f"gb-element-{uid} {cn_attr}"
    attrs = {
        "uniqueId": uid,
        "tagName": tag,
        "styles": styles,
        "css": css,
        "className": cn_attr,
    }
    if html_attributes:
        attrs["htmlAttributes"] = html_attributes
    dom_ex = _dom_attr_str(html_attributes)
    return (
        f"<!-- wp:generateblocks/element {j(attrs)} -->\n"
        f'<{tag} class="{html_class}"{dom_ex}>{inner}</{tag}>\n'
        f"<!-- /wp:generateblocks/element -->\n"
    )


def media_block(uid):
    st = {"display": "block", "height": "auto", "maxWidth": "100%"}
    css = f".gb-m-{uid}{{display:block;height:auto;max-width:100%}}"
    ha = {"src": "{{featured_image key:url}}", "alt": "{{post_title}}"}
    cn = f"gb-m-{uid}"
    return (
        f'<!-- wp:generateblocks/media {{"uniqueId":"{uid}","tagName":"img",'
        f'"styles":{j(st)},"css":"{css}","htmlAttributes":{j(ha)},"className":{json.dumps(cn)}}} -->\n'
        f'<img class="{cn}" src="{{{{featured_image key:url}}}}" alt="{{{{post_title}}}}" />\n'
        f"<!-- /wp:generateblocks/media -->\n"
    )


def gb_kv_grid(prefix, rows):
    """Label | value rows: div + CSS Grid (GB Element does not allow table/tr/td — avoids block recovery)."""
    wrap_uid = prefix + "gr"
    wrap_st = {"display": "flex", "flexDirection": "column", "width": "100%"}
    wrap_css = f".gb-element-{wrap_uid}{{display:flex;flex-direction:column;width:100%}}"
    parts = []
    i = 0
    for lab, val in rows:
        if lab == "—" and val == "—":
            sp = f"{prefix}sp{i}"
            parts.append(
                element_block(
                    sp,
                    "div",
                    {"height": "8px", "minHeight": "8px"},
                    f".gb-element-{sp}{{height:8px;min-height:8px}}",
                    "",
                )
            )
        else:
            row_uid = f"{prefix}rw{i}"
            row_st = {
                "display": "grid",
                "gridTemplateColumns": "minmax(0, min(36%, 12rem)) 1fr",
                "columnGap": "14px",
                "alignItems": "start",
                "paddingTop": "8px",
                "paddingBottom": "8px",
                "borderBottomWidth": "1px",
                "borderBottomStyle": "solid",
                "borderBottomColor": "#e0e0e0",
                "width": "100%",
            }
            row_css = (
                f".gb-element-{row_uid}{{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;"
                f"display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}}"
                f"@media (max-width:640px){{.gb-element-{row_uid}{{grid-template-columns:1fr}}}}"
            )
            cell_l = text_block(f"{prefix}lk{i}", "div", lab, mb="0px", extra={"fontWeight": "600", "fontSize": "14px"})
            cell_v = text_block(f"{prefix}vk{i}", "div", val, mb="0px", extra={"fontSize": "14px"})
            parts.append(element_block(row_uid, "div", row_st, row_css, cell_l + cell_v))
        i += 1
    return element_block(wrap_uid, "div", wrap_st, wrap_css, "".join(parts))


def gb_multi_column_grid(prefix, col_pairs):
    """One grid row: headers on first row, values below (repeat(n, 1fr)). col_pairs: list of (header, value_html)."""
    n = len(col_pairs)
    wrap_uid = prefix + "mc"
    st = {
        "display": "grid",
        "gridTemplateColumns": f"repeat({n}, minmax(0, 1fr))",
        "columnGap": "12px",
        "rowGap": "8px",
        "width": "100%",
        "paddingTop": "8px",
        "paddingBottom": "8px",
        "borderBottomWidth": "1px",
        "borderBottomStyle": "solid",
        "borderBottomColor": "#e0e0e0",
        "overflowX": "auto",
    }
    css = (
        f".gb-element-{wrap_uid}{{border-bottom:1px solid #e0e0e0;column-gap:12px;display:grid;"
        f"grid-template-columns:repeat({n},minmax(0,1fr));overflow-x:auto;padding:8px 0;row-gap:8px;width:100%}}"
    )
    top = "".join(
        text_block(
            f"{prefix}mh{j}",
            "div",
            hdr,
            mb="0px",
            extra={"fontWeight": "600", "fontSize": "13px"},
        )
        for j, (hdr, _v) in enumerate(col_pairs)
    )
    bottom = "".join(
        text_block(f"{prefix}mv{j}", "div", v, mb="0px", extra={"fontSize": "14px"}) for j, (_h, v) in enumerate(col_pairs)
    )
    return element_block(wrap_uid, "div", st, css, top + bottom)


def gb_title_content_row(prefix, title_inner, content_inner):
    """One grid row: title (e.g. post title) | body meta (policies, FAQs)."""
    row_uid = prefix + "rw"
    row_st = {
        "display": "grid",
        "gridTemplateColumns": "minmax(0, min(38%, 15rem)) 1fr",
        "columnGap": "16px",
        "alignItems": "start",
        "rowGap": "8px",
        "width": "100%",
    }
    row_css = (
        f".gb-element-{row_uid}{{align-items:start;column-gap:16px;display:grid;"
        f"grid-template-columns:minmax(0,min(38%,15rem)) 1fr;row-gap:8px;width:100%}}"
        f"@media (max-width:640px){{.gb-element-{row_uid}{{grid-template-columns:1fr}}}}"
    )
    t = text_block(prefix + "t", "div", title_inner, mb="0px", extra={"fontWeight": "600", "fontSize": "16px"})
    c = text_block(prefix + "c", "div", content_inner, mb="0px", extra={"fontSize": "14px"})
    inner = element_block(row_uid, "div", row_st, row_css, t + c)
    wrap_uid = prefix + "wrap"
    wrap_css = f".gb-element-{wrap_uid}{{margin-bottom:28px;padding-bottom:4px}}"
    return element_block(wrap_uid, "div", {"marginBottom": "28px"}, wrap_css, inner)


def gb_loop_item_wrap(prefix, inner):
    uid = prefix + "lsp"
    css = f".gb-element-{uid}{{margin-bottom:32px;padding-bottom:8px}}"
    return element_block(uid, "div", {"marginBottom": "32px", "paddingBottom": "8px"}, css, inner)


def text_link_block(uid, label, href_placeholder, mb="0px"):
    st = {"marginBottom": mb, "display": "inline-block"}
    css = f".gb-text-{uid}{{display:inline-block;margin-bottom:{mb}}}"
    ha = {"href": href_placeholder}
    cn_attr = f"gb-t-{uid}"
    html_class = f"gb-text gb-text-{uid} {cn_attr}"
    return (
        f'<!-- wp:generateblocks/text {{"uniqueId":"{uid}","tagName":"a",'
        f'"styles":{j(st)},"css":"{css}","htmlAttributes":{j(ha)},"className":{json.dumps(cn_attr)}}} -->\n'
        f'<a class="{html_class}" href="{href_placeholder}">{label}</a>\n'
        f"<!-- /wp:generateblocks/text -->\n"
    )


def text_link_self_block(uid, href_placeholder, mb="0px"):
    st = {"marginBottom": mb, "display": "inline-block"}
    css = f".gb-text-{uid}{{display:inline-block;margin-bottom:{mb};word-break:break-all}}"
    ha = {"href": href_placeholder}
    cn_attr = f"gb-t-{uid}"
    html_class = f"gb-text gb-text-{uid} {cn_attr}"
    return (
        f'<!-- wp:generateblocks/text {{"uniqueId":"{uid}","tagName":"a",'
        f'"styles":{j(st)},"css":"{css}","htmlAttributes":{j(ha)},"className":{json.dumps(cn_attr)}}} -->\n'
        f'<a class="{html_class}" href="{href_placeholder}">{href_placeholder}</a>\n'
        f"<!-- /wp:generateblocks/text -->\n"
    )


def gb_social_grid(prefix, items):
    """Platform | clickable URL (href + visible text = same dynamic value)."""
    wrap_uid = prefix + "sgr"
    wrap_st = {"display": "flex", "flexDirection": "column", "width": "100%"}
    wrap_css = f".gb-element-{wrap_uid}{{display:flex;flex-direction:column;width:100%}}"
    parts = []
    for i, (lab, href) in enumerate(items):
        row_uid = f"{prefix}sr{i}"
        row_st = {
            "display": "grid",
            "gridTemplateColumns": "minmax(0, min(36%, 12rem)) 1fr",
            "columnGap": "14px",
            "alignItems": "start",
            "paddingTop": "10px",
            "paddingBottom": "10px",
            "borderBottomWidth": "1px",
            "borderBottomStyle": "solid",
            "borderBottomColor": "#e0e0e0",
            "width": "100%",
        }
        row_css = (
            f".gb-element-{row_uid}{{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;"
            f"display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:10px 0;width:100%}}"
            f"@media (max-width:640px){{.gb-element-{row_uid}{{grid-template-columns:1fr}}}}"
        )
        cell_l = text_block(f"{prefix}slk{i}", "div", lab, mb="0px", extra={"fontWeight": "600", "fontSize": "14px"})
        cell_v = text_link_self_block(f"{prefix}svl{i}", href, mb="0px")
        parts.append(element_block(row_uid, "div", row_st, row_css, cell_l + cell_v))
    return element_block(wrap_uid, "div", wrap_st, wrap_css, "".join(parts))


def gb_matrix_row(prefix, row_key, cells, *, header=False):
    """Single table-style row. Each cell is str or ("link", uniqueId, href_placeholder) for self-linking URL."""
    row_uid = f"{prefix}{row_key}"
    n = len(cells)
    row_st = {
        "display": "grid",
        "gridTemplateColumns": f"repeat({n}, minmax(5.5rem, 1fr))",
        "columnGap": "10px",
        "rowGap": "6px",
        "alignItems": "start",
        "paddingTop": "10px",
        "paddingBottom": "10px",
        "borderBottomWidth": "1px",
        "borderBottomStyle": "solid",
        "borderBottomColor": "#e0e0e0",
        "width": "100%",
    }
    row_css = (
        f".gb-element-{row_uid}{{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:10px;display:grid;"
        f"grid-template-columns:repeat({n},minmax(5.5rem,1fr));padding:10px 0;row-gap:6px;width:100%}}"
    )
    extra = {"fontWeight": "600", "fontSize": "13px"} if header else {"fontSize": "14px"}
    bits = []
    for i, c in enumerate(cells):
        if isinstance(c, tuple) and len(c) == 3 and c[0] == "link":
            bits.append(text_link_self_block(c[1], c[2], mb="0px"))
        else:
            bits.append(text_block(f"{prefix}{row_key}c{i}", "div", c, mb="0px", extra=extra))
    return element_block(row_uid, "div", row_st, row_css, "".join(bits))


def nested_query_table_section(section_uid, title, post_type, loop_uid, item_uid, headers, cells, no_msg="None found."):
    """Property-scoped query: static header row + one data row per post (same columns)."""
    q_uid = section_uid + "q"
    q = {
        "post_type": [post_type],
        "posts_per_page": -1,
        "orderby": "title",
        "order": "asc",
    }
    q_scope = "pw-gb-scope-property"
    lp_cn_attr = f"gb-loop-{loop_uid}"
    li_cn_attr = f"gb-li-{item_uid}"
    lp_html_class = f"gb-looper-{loop_uid} {lp_cn_attr}"
    li_html_class = f"gb-loop-item gb-loop-item-{item_uid} {li_cn_attr}"
    tbl_uid = section_uid + "tbl"
    tbl_st = {"overflowX": "auto", "width": "100%", "marginTop": "8px"}
    tbl_css = f".gb-element-{tbl_uid}{{margin-top:8px;overflow-x:auto;width:100%}}"
    header_row = gb_matrix_row(section_uid, "hdr", headers, header=True)
    data_row = gb_matrix_row(section_uid, "row", cells, header=False)
    inner_looper = (
        f'<!-- wp:generateblocks/looper {{"uniqueId":"{loop_uid}","tagName":"div","className":{json.dumps(lp_cn_attr)}}} -->\n'
        + f'<div class="{lp_html_class}">\n'
        + f'<!-- wp:generateblocks/loop-item {{"uniqueId":"{item_uid}","tagName":"div","className":{json.dumps(li_cn_attr)}}} -->\n'
        + f'<div class="{li_html_class}">\n'
        + data_row
        + "</div>\n"
        + "<!-- /wp:generateblocks/loop-item -->\n"
        + "</div>\n"
        + "<!-- /wp:generateblocks/looper -->\n"
        + "<!-- wp:generateblocks/query-no-results -->\n"
        + text_block(section_uid + "nr", "p", no_msg)
        + "<!-- /wp:generateblocks/query-no-results -->\n"
    )
    inner = (
        text_block(section_uid + "h", "h2", title, mb="18px", extra={"fontSize": "20px", "fontWeight": "600"})
        + element_block(
            tbl_uid,
            "div",
            tbl_st,
            tbl_css,
            header_row
            + f'<!-- wp:generateblocks/query {{"uniqueId":"{q_uid}","tagName":"div","query":{j(q)},"className":{json.dumps(q_scope)}}} -->\n'
            + f'<div class="{q_scope}">\n'
            + inner_looper
            + "</div>\n"
            + "<!-- /wp:generateblocks/query -->\n",
        )
    )
    sec_css = SEC_CSS.replace("gb-element-sec", f"gb-element-{section_uid}")
    return element_block(section_uid, "section", SEC, sec_css, inner)


def meta_repeater_section(section_uid, title, meta_key, item_inner, no_msg="None found."):
    """GenerateBlocks Pro: Query type Post Meta (array meta, e.g. CMB2 groups). Loop item: {{loop_item key:field_id}}."""
    q_uid = section_uid + "q"
    loop_uid = section_uid + "lp"
    item_uid = section_uid + "li"
    q = {"metaKey": meta_key}
    lp_cn_attr = f"gb-loop-{loop_uid}"
    li_cn_attr = f"gb-li-{item_uid}"
    lp_html_class = f"gb-looper-{loop_uid} {lp_cn_attr}"
    li_html_class = f"gb-loop-item gb-loop-item-{item_uid} {li_cn_attr}"
    inner = (
        text_block(section_uid + "h", "h2", title, mb="18px", extra={"fontSize": "20px", "fontWeight": "600"})
        + f'<!-- wp:generateblocks/query {{"uniqueId":"{q_uid}","tagName":"div","queryType":"post_meta","query":{j(q)}}} -->\n'
        + "<div>\n"
        + f'<!-- wp:generateblocks/looper {{"uniqueId":"{loop_uid}","tagName":"div","className":{json.dumps(lp_cn_attr)}}} -->\n'
        + f'<div class="{lp_html_class}">\n'
        + f'<!-- wp:generateblocks/loop-item {{"uniqueId":"{item_uid}","tagName":"div","className":{json.dumps(li_cn_attr)}}} -->\n'
        + f'<div class="{li_html_class}">\n'
        + item_inner
        + "</div>\n"
        + "<!-- /wp:generateblocks/loop-item -->\n"
        + "</div>\n"
        + "<!-- /wp:generateblocks/looper -->\n"
        + "<!-- wp:generateblocks/query-no-results -->\n"
        + text_block(section_uid + "nr", "p", no_msg)
        + "<!-- /wp:generateblocks/query-no-results -->\n"
        + "</div>\n"
        + "<!-- /wp:generateblocks/query -->\n"
    )
    sec_css = SEC_CSS.replace("gb-element-sec", f"gb-element-{section_uid}")
    return element_block(section_uid, "section", SEC, sec_css, inner)


def nested_query_section(
    section_uid, title, post_type, loop_uid, item_uid, item_inner, no_msg="None found.", extra_scope_class=""
):
    q_uid = section_uid + "q"
    q = {
        "post_type": [post_type],
        "posts_per_page": -1,
        "orderby": "title",
        "order": "asc",
    }
    q_scope = "pw-gb-scope-property"
    if extra_scope_class:
        q_scope = f"{q_scope} {extra_scope_class}".strip()
    lp_cn_attr = f"gb-loop-{loop_uid}"
    li_cn_attr = f"gb-li-{item_uid}"
    lp_html_class = f"gb-looper-{loop_uid} {lp_cn_attr}"
    li_html_class = f"gb-loop-item gb-loop-item-{item_uid} {li_cn_attr}"
    inner = (
        text_block(section_uid + "h", "h2", title, mb="18px", extra={"fontSize": "20px", "fontWeight": "600"})
        + f'<!-- wp:generateblocks/query {{"uniqueId":"{q_uid}","tagName":"div","query":{j(q)},"className":{json.dumps(q_scope)}}} -->\n'
        + f'<div class="{q_scope}">\n'
        + f'<!-- wp:generateblocks/looper {{"uniqueId":"{loop_uid}","tagName":"div","className":{json.dumps(lp_cn_attr)}}} -->\n'
        + f'<div class="{lp_html_class}">\n'
        + f'<!-- wp:generateblocks/loop-item {{"uniqueId":"{item_uid}","tagName":"div","className":{json.dumps(li_cn_attr)}}} -->\n'
        + f'<div class="{li_html_class}">\n'
        + item_inner
        + "</div>\n"
        + "<!-- /wp:generateblocks/loop-item -->\n"
        + "</div>\n"
        + "<!-- /wp:generateblocks/looper -->\n"
        + "<!-- wp:generateblocks/query-no-results -->\n"
        + text_block(section_uid + "nr", "p", no_msg)
        + "<!-- /wp:generateblocks/query-no-results -->\n"
        + "</div>\n"
        + "<!-- /wp:generateblocks/query -->\n"
    )
    sec_css = SEC_CSS.replace("gb-element-sec", f"gb-element-{section_uid}")
    return element_block(section_uid, "section", SEC, sec_css, inner)


overview_rows = [
    ["Legal name", "{{post_meta key:_pw_legal_name}}"],
    ["Star rating", "{{post_meta key:_pw_star_rating}} / 5"],
    ["Currency", "{{post_meta key:_pw_currency}}"],
    ["Year established", "{{post_meta key:_pw_year_established}}"],
    ["Total rooms", "{{post_meta key:_pw_total_rooms}} rooms"],
]

stay_geo_l = gb_kv_grid(
    "stay",
    [
        ("Check-in", "{{post_meta key:_pw_check_in_time}}"),
        ("Check-out", "{{post_meta key:_pw_check_out_time}}"),
    ],
)
stay_geo_r = gb_kv_grid(
    "geo",
    [
        ("Latitude", "{{post_meta key:_pw_lat}}°"),
        ("Longitude", "{{post_meta key:_pw_lng}}°"),
        ("Timezone", "{{post_meta key:_pw_timezone}}"),
        ("Google Place ID", "{{post_meta key:_pw_google_place_id}}"),
    ],
)

addr_rows = [
    ["Address line 1", "{{post_meta key:_pw_address_line_1}}"],
    ["Address line 2", "{{post_meta key:_pw_address_line_2}}"],
    ["City, state, postal", "{{post_meta key:_pw_city}}, {{post_meta key:_pw_state}} {{post_meta key:_pw_postal_code}}"],
    ["Country", "{{post_meta key:_pw_country}} ({{post_meta key:_pw_country_code}})"],
]

seo_rows = [
    ["Meta title", "{{post_meta key:_pw_meta_title}}"],
    ["Meta description", "{{post_meta key:_pw_meta_description}}"],
    ["OG image (attachment ID)", "{{post_meta key:_pw_og_image}}"],
]

pool_inner = element_block(
    "rplwrap",
    "div",
    {"marginBottom": "24px"},
    ".gb-element-rplwrap{margin-bottom:24px}",
    gb_kv_grid(
        "rpl",
        [
        ("Pool name", "{{loop_item key:name}}"),
        (
            "L×W×D",
            "{{loop_item key:length_m}} m × {{loop_item key:width_m}} m × {{loop_item key:depth_m}} m",
        ),
        ("Hours", "{{loop_item key:open_time}}–{{loop_item key:close_time}}"),
        ("Heated", "{{loop_item key:is_heated}}"),
        ("Kids", "{{loop_item key:is_kids}}"),
        ("Indoor", "{{loop_item key:is_indoor}}"),
        ("Infinity", "{{loop_item key:is_infinity}}"),
        ],
    ),
)

contact_inner = element_block(
    "rctwrap",
    "div",
    {"marginBottom": "24px"},
    ".gb-element-rctwrap{margin-bottom:24px}",
    gb_kv_grid(
        "rct",
        [
            ("Label", "{{post_meta key:_pw_label}}"),
            ("Phone", "{{post_meta key:_pw_phone}}"),
            ("Mobile", "{{post_meta key:_pw_mobile}}"),
            ("WhatsApp", "{{post_meta key:_pw_whatsapp}}"),
            ("Email", "{{post_meta key:_pw_email}}"),
        ],
    ),
)

benefit_inner = element_block(
    "rbnwrap",
    "div",
    {"marginBottom": "24px"},
    ".gb-element-rbnwrap{margin-bottom:24px}",
    gb_kv_grid(
    "rbn",
    [
        ("Title", "{{loop_item key:title}}"),
        ("Description", "{{loop_item key:description}}"),
        ("Icon", "{{loop_item key:icon}}"),
    ],
    ),
)

cert_inner = element_block(
    "rcfwrap",
    "div",
    {"marginBottom": "24px"},
    ".gb-element-rcfwrap{margin-bottom:24px}",
    gb_multi_column_grid(
        "rcf",
        [
            ("Name", "{{loop_item key:name}}"),
            ("Issuer", "{{loop_item key:issuer}}"),
            ("Year", "{{loop_item key:year}}"),
            ("URL", "{{loop_item key:url}}"),
        ],
    ),
)

sus_inner = element_block(
    "rsuwrap",
    "div",
    {"marginBottom": "20px"},
    ".gb-element-rsuwrap{margin-bottom:20px}",
    gb_multi_column_grid(
        "rsu",
        [
            ("Practice", "{{loop_item key:key}}"),
            ("Status", "{{loop_item key:status}}"),
            ("Note", "{{loop_item key:note}}"),
        ],
    ),
)

acc_inner = element_block(
    "racwrap",
    "div",
    {"marginBottom": "20px"},
    ".gb-element-racwrap{margin-bottom:20px}",
    gb_multi_column_grid(
    "rac",
    [
        ("Feature", "{{loop_item key:key}}"),
        ("Status", "{{loop_item key:status}}"),
        ("Note", "{{loop_item key:note}}"),
    ],
    ),
)

room_inner = gb_loop_item_wrap(
    "rt",
    text_block("rt_t", "h3", "{{post_title}}", mb="12px", extra={"fontSize": "18px", "fontWeight": "600"})
    + text_block("rt_e", "p", "{{post_excerpt}}", mb="12px")
    + gb_kv_grid(
        "rt",
        [
            ("Rate from", "{{post_meta key:_pw_rate_from}}" + CUR),
            ("Rate to", "{{post_meta key:_pw_rate_to}}" + CUR),
            ("Max occupancy", "{{post_meta key:_pw_max_occupancy}}"),
            ("Max adults", "{{post_meta key:_pw_max_adults}}"),
            ("Max children", "{{post_meta key:_pw_max_children}}"),
            ("Size (m²)", "{{post_meta key:_pw_size_sqm}}"),
            ("Size (ft²)", "{{post_meta key:_pw_size_sqft}}"),
            ("Extra beds", "{{post_meta key:_pw_max_extra_beds}}"),
        ],
    ),
)

rest_inner = gb_loop_item_wrap(
    "rs",
    element_block(
        "rsbd",
        "div",
        {
            "paddingLeft": "18px",
            "borderLeftWidth": "3px",
            "borderLeftStyle": "solid",
            "borderLeftColor": "#c5c5c5",
        },
        ".gb-element-rsbd{border-left:3px solid #c5c5c5;padding-left:18px}",
        text_block("rs_t", "div", "{{post_title}}", mb="6px", extra={"fontSize": "17px", "fontWeight": "600"})
        + text_block(
            "rs_d",
            "div",
            "{{post_meta key:_pw_cuisine_type}} · {{post_meta key:_pw_location}} · {{post_meta key:_pw_seating_capacity}} seats",
            mb="8px",
            extra={"fontSize": "14px"},
        )
        + text_block("rs_e", "div", "{{post_excerpt}}", mb="8px", extra={"fontSize": "14px"})
        + text_link_block("rs_ru", "Reservations", "{{post_meta key:_pw_reservation_url}}", mb="4px")
        + text_link_block("rs_mu", "Menu", "{{post_meta key:_pw_menu_url}}", mb="0px"),
    ),
)

spa_inner = gb_loop_item_wrap(
    "sp",
    text_block("sp_t", "h3", "{{post_title}}", mb="12px", extra={"fontSize": "18px", "fontWeight": "600"})
    + text_block("sp_e", "p", "{{post_excerpt}}", mb="12px")
    + gb_kv_grid(
        "sp",
        [
            ("Min age", "{{post_meta key:_pw_min_age}} years"),
            ("Treatment rooms", "{{post_meta key:_pw_number_of_treatment_rooms}} rooms"),
        ],
    )
    + text_link_block("sp_bu", "Book spa", "{{post_meta key:_pw_booking_url}}", mb="4px")
    + text_link_block("sp_mu", "Spa menu", "{{post_meta key:_pw_menu_url}}", mb="0px"),
)


exp_inner = gb_loop_item_wrap(
    "ex",
    text_block("ex_t", "h3", "{{post_title}}", mb="12px", extra={"fontSize": "18px", "fontWeight": "600"})
    + text_block("ex_d", "p", "{{post_meta key:_pw_description}}", mb="12px")
    + gb_kv_grid(
        "ex",
        [
            ("Duration", "{{post_meta key:_pw_duration_hours}} h"),
            ("Price from", "{{post_meta key:_pw_price_from}}" + CUR),
            ("Complimentary", "{{post_meta key:_pw_is_complimentary}}"),
        ],
    )
    + text_link_block("ex_b", "Book", "{{post_meta key:_pw_booking_url}}", mb="0px"),
)

ev_inner = gb_loop_item_wrap(
    "ev",
    text_block("ev_t", "h3", "{{post_title}}", mb="12px", extra={"fontSize": "18px", "fontWeight": "600"})
    + text_block("ev_e", "p", "{{post_excerpt}}", mb="12px")
    + gb_kv_grid(
        "ev",
        [
            ("Start (local)", "{{post_meta key:_pw_start_datetime}}"),
            ("End (local)", "{{post_meta key:_pw_end_datetime}}"),
            ("Capacity", "{{post_meta key:_pw_capacity}} guests"),
            ("Price from", "{{post_meta key:_pw_price_from}}" + CUR),
            ("Venue (meeting room ID)", "{{post_meta key:_pw_venue_id}}"),
            ("Status", "{{post_meta key:_pw_event_status}}"),
        ],
    )
    + text_link_block("ev_b", "Booking", "{{post_meta key:_pw_booking_url}}", mb="0px"),
)

of_inner = gb_loop_item_wrap(
    "of",
    text_block("of_t", "h3", "{{post_title}}", mb="12px", extra={"fontSize": "18px", "fontWeight": "600"})
    + text_block("of_e", "p", "{{post_excerpt}}", mb="12px")
    + gb_kv_grid(
        "of",
        [
            ("Offer type", "{{post_meta key:_pw_offer_type}}"),
            ("Valid from", "{{post_meta key:_pw_valid_from}}"),
            ("Valid to", "{{post_meta key:_pw_valid_to}}"),
            ("Discount", "{{post_meta key:_pw_discount_value}} ({{post_meta key:_pw_discount_type}})"),
            ("Min. nights", "{{post_meta key:_pw_minimum_stay_nights}} nights"),
            ("Featured", "{{post_meta key:_pw_is_featured}}"),
        ],
    )
    + text_link_block("of_b", "Book / details", "{{post_meta key:_pw_booking_url}}", mb="0px"),
)

pol_inner = gb_title_content_row("pl", "{{post_title}}", "{{post_meta key:_pw_content}}")

faq_item_css = ".gb-element-fqitem{border-bottom:1px solid #ddd;margin-bottom:24px;padding-bottom:20px}"
faq_inner = element_block(
    "fqitem",
    "div",
    {"marginBottom": "24px", "paddingBottom": "20px", "borderBottomWidth": "1px", "borderBottomStyle": "solid", "borderBottomColor": "#dddddd"},
    faq_item_css,
    text_block("fqq", "div", "{{post_title}}", mb="10px", extra={"fontWeight": "600", "fontSize": "17px"})
    + text_block("fqa", "div", "{{post_meta key:_pw_answer}}", mb="0px", extra={"fontSize": "14px"}),
)

hero_left = media_block("heroimg")
hero_right = (
    text_block("h1t", "h1", "{{post_title}}", mb="12px", extra={"fontSize": "28px", "fontWeight": "700"})
    + text_block("hex", "p", "{{post_excerpt}}", mb="16px")
    + gb_kv_grid("ov", [(a, b) for a, b in overview_rows])
)

hero = element_block(
    "hero",
    "div",
    {"display": "block", "marginBottom": "16px"},
    ".gb-element-hero{display:block;margin-bottom:16px}",
    hero_left + hero_right,
)

stay_block = element_block(
    "secstay",
    "section",
    SEC,
    SEC_CSS.replace("gb-element-sec", "gb-element-secstay"),
    text_block("sth", "h2", "Stay times", mb="14px", extra={"fontSize": "20px", "fontWeight": "600"}) + stay_geo_l,
)
geo_block = element_block(
    "secgeo",
    "section",
    SEC,
    SEC_CSS.replace("gb-element-sec", "gb-element-secgeo"),
    text_block("geh", "h2", "Location &amp; geo", mb="14px", extra={"fontSize": "20px", "fontWeight": "600"}) + stay_geo_r,
)

addr_block = element_block(
    "secaddr",
    "section",
    SEC,
    SEC_CSS.replace("gb-element-sec", "gb-element-secaddr"),
    text_block("adh", "h2", "Address", mb="14px", extra={"fontSize": "20px", "fontWeight": "600"})
    + gb_kv_grid("addr", [(a, b) for a, b in addr_rows]),
)

social_list = gb_social_grid(
    "soc",
    [
        ("Facebook", "{{post_meta key:_pw_social_facebook}}"),
        ("Instagram", "{{post_meta key:_pw_social_instagram}}"),
        ("Twitter / X", "{{post_meta key:_pw_social_twitter}}"),
        ("YouTube", "{{post_meta key:_pw_social_youtube}}"),
        ("LinkedIn", "{{post_meta key:_pw_social_linkedin}}"),
        ("Tripadvisor", "{{post_meta key:_pw_social_tripadvisor}}"),
    ],
)

social_sec = element_block(
    "secsoc",
    "section",
    SEC,
    SEC_CSS.replace("gb-element-sec", "gb-element-secsoc"),
    text_block("sch", "h2", "Social", mb="14px", extra={"fontSize": "20px", "fontWeight": "600"}) + social_list,
)

amenities_table = nested_query_table_section(
    "ch_am",
    "Amenities &amp; services",
    "pw_amenity",
    "lop_am",
    "lit_am",
    ["Name", "Type", "Category", "Complimentary", "Description"],
    [
        "{{post_title}}",
        "{{post_meta key:_pw_type}}",
        "{{post_meta key:_pw_category}}",
        "{{post_meta key:_pw_is_complimentary}}",
        "{{post_meta key:_pw_description}}",
    ],
    no_msg="No amenities.",
)

nearby_table = nested_query_table_section(
    "ch_nr",
    "Nearby",
    "pw_nearby",
    "lop_nr",
    "lit_nr",
    ["Name", "Summary", "Distance", "Travel", "Lat", "Lng", "Link"],
    [
        "{{post_title}}",
        "{{post_excerpt}}",
        "{{post_meta key:_pw_distance_km}} km",
        "{{post_meta key:_pw_travel_time_min}} min",
        "{{post_meta key:_pw_lat}}°",
        "{{post_meta key:_pw_lng}}°",
        ("link", "ch_nrplink", "{{post_meta key:_pw_place_url}}"),
    ],
    no_msg="No nearby places.",
)

meeting_table = nested_query_table_section(
    "ch_mr",
    "Meeting &amp; event spaces",
    "pw_meeting_room",
    "lop_mr",
    "lit_mr",
    [
        "Space",
        "Theatre",
        "Classroom",
        "Boardroom",
        "U-shape",
        "Area (m²)",
        "Area (ft²)",
        "Natural light",
    ],
    [
        "{{post_title}}",
        "{{post_meta key:_pw_capacity_theatre}} guests",
        "{{post_meta key:_pw_capacity_classroom}} guests",
        "{{post_meta key:_pw_capacity_boardroom}} guests",
        "{{post_meta key:_pw_capacity_ushape}} guests",
        "{{post_meta key:_pw_area_sqm}} m²",
        "{{post_meta key:_pw_area_sqft}} ft²",
        "{{post_meta key:_pw_natural_light}}",
    ],
    no_msg="No meeting spaces.",
)

seo_sec = element_block(
    "secseo",
    "section",
    SEC,
    SEC_CSS.replace("gb-element-sec", "gb-element-secseo"),
    text_block("seoh", "h2", "SEO &amp; sharing", mb="14px", extra={"fontSize": "20px", "fontWeight": "600"})
    + gb_kv_grid("seo", [(a, b) for a, b in seo_rows]),
)

pc_sec = element_block(
    "secpc",
    "section",
    SEC,
    SEC_CSS.replace("gb-element-sec", "gb-element-secpc"),
    text_block("pch", "h2", "Property content", mb="14px", extra={"fontSize": "20px", "fontWeight": "600"})
    + text_block("pcc", "div", "{{post_content}}", mb="0px"),
)

shell_inner = (
    hero
    + stay_block
    + geo_block
    + addr_block
    + nested_query_section(
        "rp_ct",
        "Contacts",
        "pw_contact",
        "rp_ctlp",
        "rp_ctli",
        contact_inner,
        no_msg="No contacts.",
        extra_scope_class="pw-gb-contact-filter-property",
    )
    + social_sec
    + seo_sec
    + meta_repeater_section("rp_bn", "Direct booking benefits", "_pw_direct_benefits", benefit_inner, no_msg="No benefits listed.")
    + meta_repeater_section("rp_pl", "Pools", "_pw_pools", pool_inner, no_msg="No pools.")
    + meta_repeater_section("rp_cf", "Certifications &amp; awards", "_pw_certifications", cert_inner, no_msg="No certifications.")
    + meta_repeater_section("rp_su", "Sustainability", "_pw_sustainability_items", sus_inner, no_msg="No sustainability rows.")
    + meta_repeater_section("rp_ac", "Accessibility", "_pw_accessibility_items", acc_inner, no_msg="No accessibility rows.")
    + pc_sec
    + nested_query_section("ch_rt", "Room types", "pw_room_type", "lop_rt", "lit_rt", room_inner)
    + nested_query_section("ch_rs", "Restaurants", "pw_restaurant", "lop_rs", "lit_rs", rest_inner)
    + nested_query_section("ch_sp", "Spas", "pw_spa", "lop_sp", "lit_sp", spa_inner)
    + meeting_table
    + amenities_table
    + nested_query_section("ch_ex", "Experiences", "pw_experience", "lop_ex", "lit_ex", exp_inner)
    + nearby_table
    + nested_query_section("ch_ev", "Events", "pw_event", "lop_ev", "lit_ev", ev_inner)
    + nested_query_section("ch_of", "Offers", "pw_offer", "lop_of", "lit_of", of_inner)
    + nested_query_section("ch_pl", "Policies", "pw_policy", "lop_pl", "lit_pl", pol_inner)
    + nested_query_section("ch_fq", "FAQs", "pw_faq", "lop_fq", "lit_fq", faq_inner)
)

shell = element_block("shell", "div", SHELL, SHELL_CSS, shell_inner)

_OQ, _OL, _OI = "f3a21c8b", "e9d84b2c", "7c51a9fd"
_lp_attr = f"gb-loop-{_OL}"
_li_attr = f"gb-li-{_OI}"
_lp_html = f"gb-looper-{_OL} {_lp_attr}"
_li_html = f"gb-loop-item gb-loop-item-{_OI} {_li_attr}"
_nr_attr = "gb-t-41ac9e63"
_nr_html = f"gb-text gb-text-41ac9e63 {_nr_attr}"

loop_item = (
    f'<!-- wp:generateblocks/loop-item {{"uniqueId":"{_OI}","tagName":"div","styles":{{}},"css":"","className":{json.dumps(_li_attr)}}} -->\n'
    f'<div class="{_li_html}">\n'
    f"{shell}"
    f"</div>\n"
    f"<!-- /wp:generateblocks/loop-item -->\n"
)

out = (
    f'<!-- wp:generateblocks/query {{"uniqueId":"{_OQ}","tagName":"div","query":{{"post_type":["pw_property"],"posts_per_page":1,"orderby":"date","order":"desc"}}}} -->\n'
    "<div>\n"
    f'<!-- wp:generateblocks/looper {{"uniqueId":"{_OL}","tagName":"div","className":{json.dumps(_lp_attr)}}} -->\n'
    f'<div class="{_lp_html}">\n'
    f"{loop_item}"
    "</div>\n"
    "<!-- /wp:generateblocks/looper -->\n"
    "<!-- wp:generateblocks/query-no-results -->\n"
    f'<!-- wp:generateblocks/text {{"uniqueId":"41ac9e63","tagName":"p","styles":{{}},"css":"","className":{json.dumps(_nr_attr)}}} -->\n'
    f'<p class="{_nr_html}">No property found.</p>\n'
    "<!-- /wp:generateblocks/text -->\n"
    "<!-- /wp:generateblocks/query-no-results -->\n"
    "</div>\n"
    "<!-- /wp:generateblocks/query -->\n"
)

OUT.write_text(out, encoding="utf-8", newline="\n")
print("Wrote", OUT)
