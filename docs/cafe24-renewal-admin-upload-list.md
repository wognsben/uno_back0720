<!--
  Cafe24 renewal admin upload list.
  Documents exactly which local files should be uploaded to the Cafe24 /www tree for the current renewal-admin checkpoint.
  Focuses on the shared admin layout and product hub rollout, and separates required files from optional API files.
  This prevents accidentally uploading old project residue or unrelated legacy admin files.
-->

# Cafe24 Renewal Admin Upload List

Current checkpoint: shared renewal admin layout + product hub + semi-package boarding-pass schedule modal.

Upload target root: `/www`

## Required Uploads For This Checkpoint

Upload these files exactly:

| Local file | Cafe24 target |
| --- | --- |
| `C:\Users\wogns\OneDrive\Desktop\uno\deploy\cafe24-www\admin\renewal\_layout.php` | `/www/admin/renewal/_layout.php` |
| `C:\Users\wogns\OneDrive\Desktop\uno\deploy\cafe24-www\admin\renewal\product-edit.php` | `/www/admin/renewal/product-edit.php` |
| `C:\Users\wogns\OneDrive\Desktop\uno\deploy\cafe24-www\api\admin\product-editor.php` | `/www/api/admin/product-editor.php` |

These are the only required files for checking the new product-hub and semi-package schedule editing direction.

## Already Existing But Should Stay Uploaded

Keep these existing renewal admin/API files on the server. Re-upload them only if the server version is older than the local version.

| Local file | Cafe24 target |
| --- | --- |
| `C:\Users\wogns\OneDrive\Desktop\uno\deploy\cafe24-www\admin\renewal\_guard.php` | `/www/admin/renewal/_guard.php` |
| `C:\Users\wogns\OneDrive\Desktop\uno\deploy\cafe24-www\admin\renewal\index.php` | `/www/admin/renewal/index.php` |
| `C:\Users\wogns\OneDrive\Desktop\uno\deploy\cafe24-www\admin\renewal\products.php` | `/www/admin/renewal/products.php` |
| `C:\Users\wogns\OneDrive\Desktop\uno\deploy\cafe24-www\admin\renewal\product-navigation.php` | `/www/admin/renewal/product-navigation.php` |
| `C:\Users\wogns\OneDrive\Desktop\uno\deploy\cafe24-www\admin\renewal\product-mapping.php` | `/www/admin/renewal/product-mapping.php` |
| `C:\Users\wogns\OneDrive\Desktop\uno\deploy\cafe24-www\api\admin\products.php` | `/www/api/admin/products.php` |
| `C:\Users\wogns\OneDrive\Desktop\uno\deploy\cafe24-www\api\admin\product-mapping.php` | `/www/api/admin/product-mapping.php` |
| `C:\Users\wogns\OneDrive\Desktop\uno\deploy\cafe24-www\api\admin\product-navigation.php` | `/www/api/admin/product-navigation.php` |
| `C:\Users\wogns\OneDrive\Desktop\uno\deploy\cafe24-www\api\admin\product-navigation-seed.php` | `/www/api/admin/product-navigation-seed.php` |

## Test URLs After Upload

Primary check for this checkpoint:

1. Open `http://wognsben19997.mycafe24.com/admin/renewal/products.php`.
2. Click a product's `수정` button.
3. Confirm the URL opens like `http://wognsben19997.mycafe24.com/admin/renewal/product-edit.php?legacyProductId=63`.
4. Confirm the product hub shows `기본 정보`, `썸네일 / 이미지`, `요금 / 옵션`, `상세 내용 / 코스`, `운영 안내`, and `누락 검토`.
5. For a semi-package product, open `세미패키지 일정` and confirm only itinerary fields are shown. Seat, airfare code, airline label, and extra-fee fields should not appear in this modal.
6. Save one itinerary and confirm existing fee/seat values are not overwritten unless a future fee editor explicitly edits them.

Legacy check list below may contain older labels and is kept only for historical comparison.

Open these in order:

1. `http://wognsben19997.mycafe24.com/admin/renewal/products.php`
2. Click a product's `수정` button.
3. Confirm the URL opens like:
   `http://wognsben19997.mycafe24.com/admin/renewal/product-edit.php?legacyProductId=63`
4. Confirm the product hub shows:
   - shared UNO admin header
   - product title and summary
   - 기본 정보
   - 썸네일 / 이미지
   - 요금 / 옵션
   - 상세 내용 / 코스
   - 세미패키지 일정 or 데일리투어 캘린더
   - 운영 안내
   - 누락 검토

## Do Not Upload For This Checkpoint

Do not upload these unless we explicitly decide to rebuild them:

- legacy `/www/admin/_org`
- old `*.bak` files
- `*_pyj.php` backup/test variants
- `officefind_*` related files
- unrelated `v2_obj_info` / property CRM code
- `board_test.php`
- old other-brand mail/SMS files

## Local Verification

PHP syntax checked locally with:

- `C:\tools\php84\php.exe -l backend-bridge/admin-pages/renewal/_layout.php`
- `C:\tools\php84\php.exe -l backend-bridge/admin-pages/renewal/product-edit.php`
- `C:\tools\php84\php.exe -l backend-bridge/admin-pages/renewal/index.php`
- `C:\tools\php84\php.exe -l backend-bridge/admin-pages/renewal/products.php`
- `C:\tools\php84\php.exe -l backend-bridge/admin-pages/renewal/product-navigation.php`
- `C:\tools\php84\php.exe -l backend-bridge/admin-pages/renewal/product-mapping.php`

All checked files passed syntax validation.
