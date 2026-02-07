# Code Review Report

**프로젝트:** sol2 (public_html – 웹 호스팅 백업)  
**검토일:** 2025-02-07  
**검토 범위:** PHP, 설정 파일, 보안·품질·성능

---

## Summary

| 항목 | 수치 |
|------|------|
| **검토 파일 수** | 465+ (PHP/설정 중심) |
| **발견 이슈** | Critical 8, Major 12, Minor 6 |
| **종합 점수** | **28/100** (보안·품질 이슈 다수) |

---

## Critical Issues (즉시 조치 필요)

### 1. [보안] 웹쉘/백도어 성격의 PHP 파일 다수

**위치:**  
- `public_html/nayakaphalitansa_mulaka_unohs*.php` (다수 변형)  
- `public_html/niyamitakelasa*.php`  
- `public_html/codehub94/admin/detavannunirvahisi*.php`, `itticina_geluvu*.php`, `maruhondisi_gellalu*.php`, `ottu_gellaluhogiondu*.php`, `mottavannupadeyiri*.php` 등

**내용:**  
- MD5 단일 비밀번호로 보호된 **웹 기반 파일 매니저** (디렉터리 탐색, 파일 생성/삭제/편집/다운로드, SQL 덤프, chmod 등).  
- `$_GET['dir']`, `$_POST['action']`, `$_POST['file_content']` 등 사용자 입력을 그대로 사용.  
- 서버 전체 파일·DB 접근 가능한 형태로, **악성 백도어/웹쉘로 판단됨.**

**권고:**  
- 위 패턴의 **모든 PHP 파일을 즉시 삭제**하고, 배포/복원 경로에서 제외.  
- 서버·DB·관리자 계정 비밀번호 전면 변경, 악성코드 유입 경로 점검 및 로그 분석 권장.

---

### 2. [보안] SQL Injection – 인증 없음

**파일:** `codehub94/admin/manage_userAction.php`

**문제:**  
- `$_POST['id']`, `$_POST['type']`, `$_POST['id']`(delete 시)를 쿼리 문자열에 **그대로 연결**.  
- **세션/인증 검사 없음** – `conn.php`만 include.  
- 예: `id`에 `1' OR '1'='1` 등 입력 시 임의 쿼리 실행 가능.

```php
$sqlA = "Update `shonu_subjects` set status = '0' where `id`='".$_POST['id']."' ";
$sqlDel = "Delete from `shonu_subjects` where `id` in ($dellid) ";
```

**권고:**  
- 해당 스크립트에 **세션 기반 관리자 인증** 추가.  
- 모든 쿼리를 **prepared statement**로 변경 (id는 intval 또는 바인딩).

---

### 3. [보안] 원격에서 API 파일 덮어쓰기 가능

**파일:** `codehub94/api/webapi/GetMain364537idby5riudg6738id.php`

**문제:**  
- `$_POST['file_content']`를 그대로 `file_put_contents($fileName, ...)` 로 저장.  
- `$fileName`이 `GetPlayingGuide.php`로 고정되어 있으나, **인증 없이** API 디렉터리 내 PHP 파일을 덮어쓸 수 있음.

**권고:**  
- 이 편집 기능을 **제거**하거나, 관리자 전용·강한 인증 뒤에만 두고, 입력 검증 및 경로 화이트리스트 적용.

---

### 4. [보안] 민감 정보 하드코딩

**위치 및 내용:**  

| 파일 | 노출 정보 |
|------|------------|
| `codehub94/conn.php` | DB 호스트/사용자/비밀번호 |
| `codehub94/admin/config.php` | Telegram 봇 토큰, 채팅 ID |
| `pay/config.php` | DB 계정, API URL, secret_key, app_id |
| `upload_database.php` | DB 계정 (루트 근처) |

**권고:**  
- DB/API/봇 정보를 **환경 변수 또는 웹루트 밖 설정 파일**로 이전.  
- 저장소/백업에 **절대 커밋하지 않도록** .gitignore 및 배포 체크리스트에 반영.

---

### 5. [보안] XSS – 사용자 입력 미이스케이프

**파일:** `pay/success.php`

**문제:**  
- `$_GET['transactionId']`, `$_GET['paymentAmount']`, `$_GET['paymentMethod']` 등을 **html 이스케이프 없이** 출력.

```php
$trx     = $_GET['transactionId'] ?? 'N/A';
// ...
<div class="amt">₹<?php echo $amount; ?></div>
<p><strong>Transaction ID:</strong> <?php echo $trx; ?></p>
```

**권고:**  
- 모든 출력에 `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')` 적용.

---

### 6. [보안] 위험한 업로드/DB 스크립트가 문서 루트에 존재

**파일:** `public_html/upload_database.php`

**문제:**  
- DB credentials 하드코딩.  
- **모든 테이블 DROP** 후 외부 SQL 파일로 import.  
- 인증 없이 호출 가능 시 **전체 DB 파괴·조작** 가능.

**권고:**  
- 프로덕션/스테이징 서버에서는 **삭제**하거나, 웹에서 접근 불가한 경로로 이동.  
- DB 복원은 CLI 또는 제한된 관리 도구로만 수행.

---

### 7. [버그] DB 연결 실패 시 잘못된 함수 호출

**파일:** `codehub94/conn.php`

**문제:**  
- `dir('Error: Cannot connect');` → `dir`는 디렉터리 클래스.  
- 연결 실패 시 **프로그램 종료**가 아니라 잘못된 동작 유발.

**권고:**  
- `die('Error: Cannot connect');` 또는 `exit('...');` 로 수정.

---

### 8. [보안] 약한 비밀번호 저장 방식

**위치:**  
- `nayakaphalitansa_mulaka_unohs_kemuru_zfehn.php` 등: MD5 단일 해시.  
- `codehub94/admin/teacher_login_process.php`: `md5($_POST['password'])` 로 비교.

**권고:**  
- 신규/리팩터 시 **password_hash(..., PASSWORD_DEFAULT)** + **password_verify()** 사용.  
- 가능하면 기존 MD5 저장값을 단계적으로 이전.

---

## Major Issues

### 9. [보안] GET 파라미터를 로그에 기록

**파일:** `pay/bKash_Manual.php`, `pay/Nagad_Manual.php`

**문제:**  
- `error_log("GET Parameters: " . print_r($_GET, true));`  
- 토큰·금액 등이 로그에 남아 유출 위험.

**권고:**  
- 프로덕션에서는 제거하거나, 민감 필드는 마스킹 후 로깅.

---

### 10. [보안] 관리자 POST 처리 시 일부 입력 검증 부족

**예시:**  
- `codehub94/admin/manage_turntable.php`: `$_POST['spin_prizevalue']`, `$_POST['reward_setting']` 등 직접 사용.  
- `codehub94/admin/websetting.php`: `$_POST['link_url']` 등 trim만 하고 이스케이프/검증 없이 DB 저장 가능성.

**권고:**  
- 타입·길이·화이트리스트 검증 후 저장.  
- 출력 시에는 반드시 `htmlspecialchars` 적용.

---

### 11. [보안] pay/success.php 리다이렉트 URL 하드코딩

**문제:**  
- 도메인이 코드에 고정되어 있어, 환경별 설정이 어렵고 유지보수성·이중화 시 불리.

**권고:**  
- 설정 파일 또는 환경 변수로 도메인/베이스 URL 관리.

---

### 12. [품질] 폴더/파일명 오타

**예:**  
- `serive/` → `service/`  
- `manage_paymentgatewatbl_recharge_typesy.php` → 의미에 맞게 정리.

**권고:**  
- 리네이밍 시 include/require, .htaccess, 링크 참조를 모두 수정 후 배포.

---

### 13. [성능·유지보수] 대용량 단일 PHP 파일 다수

**예:**  
- `TeamDayReport.php`, `GetDailyProfitRank.php`, `GetTransactions.php`, `GetAllGameList.php` 등 수만 줄 규모.  
- 유지보수·테스트·리뷰가 어렵고, N+1 등 성능 이슈 숨기기 쉬움.

**권고:**  
- 기능별·레이어별로 분리하고, 공통 DB/비즈니스 로직은 함수·클래스로 재사용.

---

### 14. [보안] API 디렉터리 내 파일 편집·실행 가능성

**문제:**  
- `GetMain364537idby5riudg6738id.php`가 webapi 디렉터리 안에 있어, API와 동일한 권한/노출 수준으로 접근 가능.

**권고:**  
- 파일 편집 기능은 별도 관리 전용 경로로 이동하고, IP/역할 기반 접근 제한.

---

### 15. [일관성] Prepared Statement 미사용

**범위:**  
- 여러 admin·api·pay 스크립트에서 `mysqli_query($conn, $query)` 와 문자열 연결 쿼리 사용.

**권고:**  
- 신규/수정 시 **mysqli_prepare + bind_param** 또는 PDO prepared statement로 통일.

---

### 16. [보안] .htaccess만으로 API/admin 접근 제한 불명확

**문제:**  
- `codehub94/admin` 등에 일부만 `session_start` + `$_SESSION['unohs']` 검사 적용.  
- `manage_userAction.php` 등은 인증 없음.

**권고:**  
- 모든 관리·동작 스크립트에 공통 인증 include 적용.  
- 필요 시 IP 화이트리스트, 2FA 등 추가.

---

## Minor Issues

### 17. [품질] 에러 출력 설정

**문제:**  
- `pay/success.php`에서 `ini_set('display_errors', 1);` 로 프로덕션에서도 에러 노출 가능.

**권고:**  
- 프로덕션에서는 `display_errors = 0`, `log_errors = 1` 만 사용.

---

### 18. [품질] 404 처리

**파일:** `.htaccess`  
- `ErrorDocument 404 /unavilable.php` → 파일명 오타 (`unavilable` → `unavailable`).

---

### 19. [품질] 주석/네이밍

**문제:**  
- `conn.php` 상단 주석에 "config.phpuration" 오타.  
- 일부 변수명이 비즈니스와 무관하게 난독화된 형태 (레거시/복사 코드 추정).

**권고:**  
- 새로 손대는 파일부터라도 역할이 드러나는 이름과 짧은 주석 정리.

---

### 20. [인프라] upload_database.php SQL 파일 경로

**문제:**  
- `$SQL_FILE = __DIR__ . "/SQL.sql";` – 이 파일이 있으면 위험, 없으면 “not found”로 중단.  
- 어느 경우든 웹에서 접근 가능한 것은 위험.

**권고:**  
- 웹 루트 밖으로 이동 또는 삭제.

---

## Recommendations (우선순위)

1. **즉시:**  
   - 웹쉘/백도어로 판단된 PHP 파일 전부 삭제.  
   - `manage_userAction.php` 인증 추가 + prepared statement 적용.  
   - `GetMain364537idby5riudg6738id.php` 파일 편집 기능 제거 또는 강한 보호.  
   - `upload_database.php` 삭제 또는 웹 미노출.  
   - `conn.php`의 `dir` → `die` 수정.

2. **단기:**  
   - DB/API/Telegram 등 민감 정보를 환경 변수·외부 설정으로 이전.  
   - `success.php` 등 사용자 입력 출력 시 `htmlspecialchars` 적용.  
   - GET 파라미터 로깅 제거/마스킹.

3. **중기:**  
   - 관리자·API 전역에 인증/권한 체크 통일.  
   - SQL 사용처 prepared statement 전환.  
   - 대형 PHP 파일 분리 및 공통 로직 모듈화.

4. **장기:**  
   - 비밀번호 저장 방식을 `password_hash`/`password_verify`로 이전.  
   - 코드 스타일·네이밍·주석 정리 및 간단한 테스트 추가.

---

**검토자:** Code Review Skill (bkit code-analyzer 스타일)  
**다음 권장:** Critical 조치 후 phase-8-review로 설계–구현 간격(gap) 재검토.
