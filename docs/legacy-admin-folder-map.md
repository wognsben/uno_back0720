<!--
  Legacy UNO Travel admin folder map.
  Documents the role of the old /www/admin backend, its menu groups, popup/save flows, and database touchpoints.
  Used as the checklist for rebuilding the renewal admin without missing legacy reservation, product, tour-day, site-setting, and reporting features.
  This is a planning/audit document only; implementation code should live in backend-bridge/admin-pages and backend-bridge/php-api.
-->

# Legacy Admin Folder Map

Source reviewed: `C:\Users\wogns\OneDrive\Desktop\우노\26.06.18 우노\www\admin`

The legacy admin is not one clean module. It is a mix of page wrappers, board pages, popup editors, AJAX/save handlers, and old copied modules. For the renewal admin, we should not copy the structure as-is. We should preserve the data flow and rebuild the screens around a clearer menu.

## Folder Roles

| Path | Role | Renewal decision |
| --- | --- | --- |
| `/admin/*.php` | Main admin pages such as dashboard, reservation list, product list, site settings, tour settings, reports. | Use as functional source of truth. Rebuild UI under `/admin/renewal`. |
| `/admin/_side_menu.php` | Legacy left menu definition and route grouping. | Use to define renewal menu coverage. |
| `/admin/include/` | Shared header/footer/layout fragments. | Replace with renewal shell, keep permission/session checks. |
| `/admin/include_files/` | Partial views, AJAX handlers, save/update scripts. Many actual mutations live here. | Audit before replacing any editor. These are important. |
| `/admin/popup/` | Fancybox popup editors for reservation/product options/schedules. | Renewal should use modal/popup pattern for dense editors. |
| `/admin/ajax/`, `/admin/api/` | Older AJAX and automation endpoints. | Keep only confirmed active endpoints. |
| `/admin/css`, `/admin/js`, `/admin/images` | Legacy admin assets and libraries. | Do not copy visually. Use only for behavior clues if needed. |
| `/admin/_org` | Old/organization style module copies. | Low priority unless a live menu links to it. |

## Main Menu Coverage

### Dashboard

Legacy files:

- `main.php`
- `include_files/admin_main.php`

Current dashboard data:

- Today's reservation counts from `tour_reg`
- Inquiry/review counts from `g5_write_*` board tables
- Recent reservation amount/count statistics from `tour_reg.total_fee1`
- Visitor statistics from `g5_visit_sum`

Renewal requirement:

- Keep the same business summary, but make it calmer and more scannable.
- Header navigation must always be visible.
- Do not visually clone the old admin; use the data with a cleaner layout.

### Reservation Management

Legacy files:

- `booking.php`
- `_save_booking.php`
- `popup/pop_content.php?gubun=booking`
- `include_files/setReg.php`
- `include_files/_modifyRes.php`
- `include_files/_setGuide.php`
- `include_files/_reSendVoucher.php`
- `regist_calendar.php`
- `regist_weekly.php`
- `kscardPayList.php`
- B2B reservation/account files such as `b2b_invoice.php`, `b2b_account.php`

Core tables:

- `tour_reg`
- `tour_reg_count`
- `g5_write_product`
- `g5_member`
- `kspay_result`
- `tour_fee_b2b`
- `pay_result`
- `tour_account`

Important status values found:

| Status | Meaning in admin UI |
| --- | --- |
| `1` | 예약 대기 / 신청 |
| `2` | 예약 확인 / 입금 |
| `3` | 예약 확정 |
| `9` | 예약 취소 |
| `91` | 예약 취소 요청 |
| `95` | 예약 취소 - 미결제 |
| `96` | 예약 취소 - 투어 불가능 |
| `99` | cancellation bucket used in stats |
| `cart`, `booking` | temporary cart/booking states, excluded from normal admin list |

Renewal requirement:

- Reservation edit must preserve applicant info, tour info, guide assignment, memo, status change, voucher resend, payment/deposit information, and cancellation handling.
- The reservation list should split or filter semi package and daily tour clearly.
- Card payment history must consider both the old `pay_result` table and the KSNET `kspay_result` table.
- Reservation cost/original-cost editing must be checked through `tour_account` and `tour_reg.fee_wonga` before any replacement screen is built.

### Member Management

Legacy files:

- `member.php`
- `member_form.php`
- `include_files/member*.php`
- B2B member list fragments in `include_files/`

Core tables:

- `g5_member`
- Reservation joins through `tour_reg`

Renewal requirement:

- Keep member list and profile edit.
- Keep admin/B2B/guide distinctions if they are actively used.
- Do not invent new auth data separate from Gnuboard member data.

### Product Management

Legacy files:

- `board.php?bo_table=product`
- `write.php`
- `_save_product.php`
- `tourCourse.php`
- `pkgConfig.php`
- `productDispIndex.php`
- `popup/pop_content.php?gubun=option`
- `popup/pop_content.php?gubun=pkgSkd`
- `include_files/_modifyProductConf.php`

Core tables:

- `g5_write_product`
- `v2_product_options`
- `v2_pkgTour`
- `v2_pkgTourSkd`
- `g5_board_file`
- `tour_quota`
- `tour_schedule`
- `g5_write_tour_schedule`

Important product areas that must not be lost:

- Product title/category/visibility/order/thumbnail
- Normal price, deposit, local payment, extra/special price fields
- Product options by age/condition
- Guide or guide-related assignment data where used
- Detail content/images/course content from `tourCourse.php`
- Popup-managed options, not only inline text fields
- Front exposure, hidden, and preparing states for renewal frontend
- Semi package quota and departure schedule data
- Legacy tour schedule data, if it is still connected to active semi package products

Renewal requirement:

- Product edit page should be a clean management hub.
- Dense editors should open as modals/popups:
  - Basic product info
  - Thumbnail/media
  - Price/options
  - Detail content/course
  - Semi package boarding-pass style itinerary
  - Daily tour monthly calendar
  - Operation notices/options
- Do not place all legacy fields in one huge vertical form.
- Before rebuilding a field, confirm whether it belongs to the active UNO Travel product flow or to an old copied module.

### Semi Package Operation

Legacy files:

- `pkgConfig.php`
- `popup/pop_content.php?gubun=pkgSkd`
- `_save_pkgTour.php`

Core tables:

- `v2_pkgTour`
- `v2_pkgTourSkd`
- `g5_write_product`
- `tour_quota`
- `tour_schedule`
- `g5_write_tour_schedule`

Renewal requirement:

- Boarding pass is presentation for itinerary, not an airline ticket editor.
- It should manage only schedule-like data:
  - Departure place / origin
  - Departure time
  - Arrival place / destination
  - Arrival schedule/date
  - Optional short label/memo if the frontend needs it
- Remove unrelated fields such as seat, airline-style status, extra fee, and KE labels unless the frontend actually uses them.
- If there are outbound/return style sections, label them as itinerary legs, not flight sales.
- `tour_quota`, `tour_schedule`, and `g5_write_tour_schedule` exist in the legacy admin. Their active usage must be verified product-by-product before deciding whether the renewal boarding-pass editor reads from them or from `v2_pkgTour`/`v2_pkgTourSkd`.

### Daily Tour Operation

Legacy files:

- `tourClose.php`
- `tourFee.php`
- `tourFeeTicket.php`
- `tourExchange.php`
- `regist_calendar.php`
- `regist_weekly.php`
- `include_files/_setEventData.php`
- `include_files/_setEventDataFee.php`
- `include_files/_set_max_count.php`

Core tables:

- `tour_closed_2`
- `tour_closed`
- `tour_fee`
- `tour_fee_ex`
- `tour_fee_ticket`
- `tour_reg_count`
- `tour_guide`

Renewal requirement:

- Daily tour editor needs a real month calendar, one month at a time.
- Calendar should support previous/next month.
- Each date needs clear fields only:
  - status: available / soon / sold out / closed
  - max seats
  - reserved/current seats if editable or visible
- Repeating rules should be human-friendly:
  - daily
  - odd/even dates
  - weekdays Monday through Sunday
- Fee options should not clip text or look like code.
- Keep fee/deposit/local payment management linked to the same product edit experience.
- Guide assignment has a separate flow through `tour_guide` and `tour_reg.guide_mb_no`; do not hide it inside the product fee/calendar editor without confirming the workflow.

### Site Settings

Legacy files:

- `Config.php`
- `_save_config.php`
- `productDispIndex.php`
- `newwinlist.php`
- FAQ/menu/board-related pages

Core tables:

- `g5_config`
- `g5_write_product`
- `g5_board_file`
- `g5_new_win`
- `tour_terms`
- `uno_popup`
- board tables such as FAQ/review/customer boards

Settings found in `Config.php`:

- Terms/privacy content: `cf_stipulation`, `cf_privacy`, `cf_privacy2`
- Work hours/contact: `cf_1`, `cf_2`
- Footer: `cf_3`, `cf_4`
- Cancellation/refund: `cf_cancel_pc`, `cf_cancel_m`
- Reservation notice: `cf_notice_pc`, `cf_notice_m`
- Reservation completion messages: `cf_res_ok_pc`, `cf_res_ok_pkg_pc`, `cf_res_ok_jeju_pc`, `cf_ticket_pc` and mobile variants
- SNS fields and uploaded SNS icons
- Voucher bottom content
- Recommended/best products

Renewal requirement:

- Backend must eventually edit:
  - Main page content
  - Reviews
  - FAQ
  - Terms/privacy
  - Work hours/contact
  - Footer
  - Cancellation/refund rules
  - Reservation notices
  - BRAND about UNO
  - BRAND contact
  - Community pages
- Product navigation
- Popup management through `uno_popup`
- Tour terms through `tour_terms`, if still active
- Community spam controls and bulk moderation tools

### Community Spam Moderation

Observed operational issue:

- Spam accounts can sign up and post repeated inquiry/community content every few minutes.
- Spam content includes illegal sexual content, scams, gambling links, and repeated promotional text.

Renewal requirement:

- Add bot protection to signup and community write forms.
- Prefer Cloudflare Turnstile for new renewal forms because it is easier to maintain than old image CAPTCHA and works on normal PHP hosting through server-side token verification.
- Keep KCAPTCHA only as a short-term fallback where legacy forms cannot be touched yet.
- Add rate limits:
  - per member
  - per IP
  - per board
- Add keyword/domain filtering for gambling, adult spam, scam links, repeated URLs, and known bad phrases.
- Add admin bulk actions:
  - delete all posts by selected writer
  - block selected member
  - block selected IP
  - move suspicious posts to hidden/pending state
  - review spam logs

### Reports And Misc

Legacy files:

- `report.php`
- `regist_report_product.php`
- `regist_report_tourday.php`
- `visit_*`
- `emailHistory.php`
- `biztalk.php`
- `smsHistory.php`

Core tables:

- `tour_reg`
- `g5_visit_sum`
- `pay_result`
- `kspay_result`
- `tour_account`
- mail/SMS/Biztalk log tables depending on runtime config

Renewal requirement:

- Lower priority than reservation/product/site settings.
- Keep menu placeholders early so the admin IA does not feel incomplete.

## Confirmed Legacy Tables To Preserve Or Verify

These tables were found in active-looking legacy admin files and should be treated carefully:

| Table | Area | Renewal handling |
| --- | --- | --- |
| `tour_reg` | Reservation source of truth | Preserve. |
| `g5_write_product` | Product source of truth | Preserve. |
| `g5_member` | Member/auth source of truth | Preserve. |
| `tour_fee` | Daily tour fee/deposit/local payment | Preserve. |
| `tour_closed`, `tour_closed_2` | Daily tour closed/sold-out calendar | Preserve. |
| `tour_reg_count` | Daily tour capacity | Preserve. |
| `v2_pkgTour`, `v2_pkgTourSkd` | Semi package departures/schedules | Preserve after field audit. |
| `tour_quota` | Semi package quota/capacity | Verify active usage, then preserve if active. |
| `tour_schedule`, `g5_write_tour_schedule` | Legacy tour schedule content | Verify active usage before using in boarding-pass editor. |
| `tour_guide` | Guide assignment/comment URL | Preserve if guide workflow remains active. |
| `tour_fee_b2b` | B2B fee calculation | Preserve if B2B remains active. |
| `tour_account` | Reservation original cost/cost items | Preserve for reservation accounting. |
| `pay_result` | Old PG payment history | Read-only or legacy lookup. |
| `kspay_result` | KSNET payment history | Preserve for payment lookup. |
| `g5_config` | Site settings | Preserve. |
| `tour_terms` | Tour terms | Verify active usage, then preserve if active. |
| `uno_popup` | Site popup management | Preserve if popup feature remains active. |

## Other Project Or Legacy-Residue Candidates

These files/tables appear in the backup but look like copied modules, old tests, or unrelated project residue. Do not rebuild them unless a live UNO Travel menu or active business workflow requires them.

| Candidate | Evidence | Default decision |
| --- | --- | --- |
| `officefind_banner` / `bannerform.php` | OfficeFind naming, not UNO Travel product/reservation wording | Exclude from renewal scope. |
| `v2_main_info`, `v2_obj_info`, `v2_noc` | Appears in `ajax/load_by_function.php` and `api/auto_run.php`, property/CRM style logic | Exclude unless confirmed active. |
| `frm/board_view.php`, `frm/product_view.php` | Preview-like code for other content/product formats | Low priority, verify before use. |
| `board_test.php` | Sample/static board content | Exclude or archive. |
| `*_pyj.php`, `*.bak`, dated duplicate files | Backup/test variants | Do not use as primary source. |
| Old Redpants/other-brand mail text | Email/SMS/voucher code may contain old brand strings | Replace with UNO Travel verified text before reuse. |

Rule: when a legacy file references one of these candidates, treat it as evidence of old code first, not as a renewal requirement.

## Renewal Admin Menu Proposal

Primary top navigation:

| Group | Pages |
| --- | --- |
| 예약관리 | 세미패키지 예약, 데일리투어 예약, 예약 캘린더, 1:1 문의, KSNET 카드 결제 내역 |
| 회원관리 | 회원목록, 관리자/가이드/B2B 계정 if active |
| 상품관리 | 세미패키지 상품, 데일리투어 상품, 상품 노출/준비중/숨김, 상품 상세 수정 |
| 투어 설정 | 데일리투어 캘린더, 마감/마감임박/인원, 요금 옵션, 환율/티켓 |
| 사이트 설정 | 메인, 리뷰, FAQ, 약관, 근무시간, 연락처, Footer, 취소/환불, 예약안내, BRAND |
| 기타 | 접속자, 메일 발송 목록, 비즈톡, SMS, 보고서 |

## Key Decision

The renewal admin should not be "one giant product editor." The old admin separated product editing, option popups, tour day settings, package schedules, fee settings, and detail content because each area has different operational complexity.

The better renewal pattern is:

1. Product list shows semi and daily in a two-column or tabbed work view.
2. Product edit opens as a clear hub.
3. High-density areas open as modals/popup pages.
4. All screens use one shared renewal admin header and side navigation.
5. Legacy DB tables remain the source of truth until a later migration is planned.
6. Other-project residue is explicitly excluded unless the user confirms it is still active.

## Immediate Implementation Order

1. Build a shared renewal admin shell so every renewal backend page has the same header/menu.
2. Replace current product editor structure with a product hub.
3. Move semi boarding-pass schedule into its own modal/page.
4. Move daily calendar into a real month calendar modal/page.
5. Move operation options back into popup/modal style and preserve legacy fields.
6. Add dashboard cards using the same counts as `main.php`.
7. Add community spam moderation and bot-protection planning before reopening public write forms.
8. Then expand reservation/member/site-setting pages.
