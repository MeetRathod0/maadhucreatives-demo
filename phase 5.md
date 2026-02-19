You are an expert Laravel 12 developer. This is Phase 5 — the final 
polish and QA phase. All previous phases are complete including the 
filler phase.

Do a full codebase review and fix everything listed below.
Read every relevant file before making changes.

---

## 1. GLOBAL CODE QUALITY CHECK

Read all files in these directories:
- app/Http/Controllers/Admin/
- app/Http/Controllers/Public/
- app/Services/Admin/
- app/Services/Public/
- app/Repositories/
- app/Models/
- resources/views/admin/
- resources/views/public/

Fix any of these if found:
- Any Eloquent query directly in a controller or service (move to repository)
- Any business logic directly in a controller (move to service)
- Any missing use/import statements
- Any undefined variable passed to a view
- Any hardcoded paths that should use asset() or Storage::url()
- Any raw {{ }} that should be {!! !!} or vice versa
- Any missing @csrf on forms
- Any missing @method('PUT') or @method('DELETE') on forms
- Any old() helper missing on form inputs that should repopulate on error

---

## 2. SECURITY HARDENING

- Confirm every admin route is protected by admin.auth middleware
- Confirm public routes have NO auth middleware
- Confirm {!! $blog->description !!} is ONLY used for TinyMCE HTML output
  and all other variables use {{ }}
- Confirm image upload validates mimes AND max size in both
  StoreBlogRequest and UpdateBlogRequest
- Confirm rate limiting is applied ONLY to POST /login route
- Confirm CSRF token is present in the meta tag in admin layout head
  <meta name="csrf-token" content="{{ csrf_token() }}">
  This is required for all AJAX fetch() calls in JS

---

## 3. ADMIN SIDEBAR ACTIVE STATE

Read sidebar.blade.php and verify active state logic covers all routes:
- Dashboard active when: routeIs('admin.dashboard')
- Blogs active when: routeIs('admin.blogs.*')

Also verify the sidebar highlights correctly when on:
- /admin/blogs (index)
- /admin/blogs/create
- /admin/blogs/{id}/edit
- /admin/blogs/{id}/show
- /admin/profile (neither dashboard nor blogs should be active)

---

## 4. BREADCRUMBS AUDIT

Check every admin view has correct breadcrumbs:
- dashboard/index: Home
- profile/index: Home > Profile
- blogs/index: Home > Blogs (with Add Blog button on right)
- blogs/create: Home > Blogs > Create
- blogs/edit: Home > Blogs > Edit
- blogs/show: Home > Blogs > Preview

Make sure breadcrumb Home links to route('admin.dashboard')
and Blogs links to route('admin.blogs.index').

---

## 5. SWEETALERT2 COMPLETE AUDIT

Verify SweetAlert2 is correctly wired for every action:

Admin layout flash handler (session-based toasts):
- session('success') → success toast top-end timer 3500
- session('error')   → error toast top-end timer 4000

Login page flash handler:
- session('success') → success toast (logout success message)
- session('error')   → error toast

AJAX-based toasts (inline JS):
- Blog delete confirm → confirm dialog → on success: remove <tr> + success toast
- Blog toggle status → no confirm → on success: update badge + button + success toast
- On any fetch() failure → error toast

Make sure ALL Swal.fire() calls use consistent config:
{
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
}

---

## 6. TINYMCE FINAL CHECK

Read create.blade.php and edit.blade.php and verify:
- TinyMCE loaded from /assets/vendor/tinymce/tinymce.min.js (local only)
- tinymce.init() selector targets #blog-description
- plugins include: lists link image table code wordcount
- Form submit listener calls tinymce.triggerSave() BEFORE form submits
- On edit page TinyMCE loads existing content correctly from textarea value
- TinyMCE height is at least 400px

---

## 7. IMAGE HANDLING FINAL CHECK

Read BlogService.php and verify:
- blogs/ directory is created if it does not exist before saving
- Image is converted to webp at quality 85
- Filename format is: {slug}-{timestamp}.webp
- Old image is deleted from storage when new image uploaded on update
- deleteImage() checks file exists before deleting (no error on missing file)
- Storage::disk('public') is used consistently (not Storage:: alone)

Read Blog model and verify:
- getImageUrlAttribute() falls back to asset('assets/images/placeholder.webp')
- placeholder.webp exists at public/assets/images/placeholder.webp

---

## 8. PAGINATION CONSISTENCY

Verify all paginated views use correct pagination style:
- Admin blog list: $blogs->links('pagination::bootstrap-4')
  (AdminLTE uses Bootstrap 4)
- Public blog list: $blogs->links()
  (public layout uses Bootstrap 5)

Verify withQueryString() is called on all admin paginated queries
so filters are preserved across pages.

---

## 9. PUBLIC BLOG FINAL CHECK

Read public/blog/index.blade.php and verify:
- Only active blogs shown (handled by repository scope — confirm)
- Cards have consistent height with fixed image height
- Excerpt is limited to 150 characters
- Read More links correctly to route('blog.show', $blog->slug)
- Empty state message shown when no blogs exist

Read public/blog/show.blade.php and verify:
- @section('meta') renders all metatags from $blog->metatags JSON
- og:title, og:description, og:image added automatically
- Views increment happens in controller not in view
- Previous blog link shows only if $prev exists
- Next blog link shows only if $next exists
- Description rendered with {!! $blog->description !!}
- Blog image uses $blog->image_url accessor

---

## 10. RESPONSIVE DESIGN CHECK

Verify these pages are mobile-friendly using Bootstrap classes:
- Public blog listing: 3 cols desktop, 2 cols tablet, 1 col mobile
  (col-lg-4 col-md-6 col-12)
- Public blog show: content container max-width 800px centered
- Admin blog list: table has table-responsive wrapper div
- Admin create/edit: two column layout collapses on mobile
  (col-md-8 and col-md-4 stack on small screens)
- Login page: AdminLTE login-page class handles this automatically

---

## 11. DASHBOARD STATS NULL SAFETY

Read dashboard/index.blade.php and verify:
- $stats['latest_blog'] null check before accessing ->title or ->published_date
- $stats['total_views'] displays with number_format()
- $stats['total_blogs'] and $stats['active_blogs'] display correctly when 0
- Str::limit() on latest blog title uses the title_short accessor
  ($stats['latest_blog']->title_short) not raw Str::limit() in blade

---

## 12. FORM REPOPULATION AUDIT

Check every form field in these views uses old() correctly:
- login.blade.php: email field uses old('email')
- profile/index.blade.php: all password fields are type="password"
  so they should NOT repopulate (correct — leave empty on error)
- blogs/create.blade.php: title, slug use old()
  status select uses old('status', '1') defaulting to active
- blogs/edit.blade.php: all fields use old('field', $blog->field) pattern

---

## 13. FINAL CLEANUP

- Remove any dd(), dump(), var_dump(), or ray() debug calls
- Remove any TODO or placeholder comments
- Remove any unused use/import statements in all PHP files
- Make sure all blade files have consistent indentation
- Make sure no view uses @extends and also has full HTML document tags
  (a view either extends a layout OR is a standalone HTML document — not both)

---

## 14. RUN FINAL CHECKS

After all fixes run:
php artisan route:list
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan cache:clear

Then do a complete manual test of every feature:

AUTH:
✓ /login page loads
✓ Wrong credentials shows error
✓ 5 failed attempts triggers rate limit message
✓ Correct login redirects to /admin/dashboard
✓ Logout redirects to /login with success toast
✓ Change password with wrong old password shows error
✓ Change password correctly shows success toast

DASHBOARD:
✓ All 4 stats cards show correct data
✓ Latest blog card links to edit page
✓ Zero state shows correctly when no blogs exist

BLOG CRUD:
✓ Blog list loads with correct columns
✓ Search by title works
✓ Filter by status works
✓ Filter by date range works
✓ Clear filter resets all filters
✓ Pagination works and preserves filters
✓ Create blog: all fields validate correctly
✓ Create blog: image uploads and converts to webp
✓ Create blog: TinyMCE content saves correctly
✓ Create blog: meta tags save correctly
✓ Create blog: slug auto-generates from title
✓ Create blog: success toast shows on redirect
✓ Edit blog: all fields pre-filled correctly
✓ Edit blog: new image replaces old image in storage
✓ Edit blog: meta tags pre-filled and editable
✓ Edit blog: success toast shows on redirect
✓ Delete blog: SweetAlert2 confirm shows
✓ Delete blog: row removed from table on success
✓ Delete blog: success toast shows
✓ Toggle status: badge and button update without reload
✓ Toggle status: success toast shows
✓ Admin preview: opens in new tab
✓ Admin preview: inactive blog shows warning banner

PUBLIC BLOG:
✓ /blog lists only active blogs
✓ /blog cards show image, title, author, date, read time, excerpt
✓ /blog pagination works
✓ /blog/{slug} loads full blog post
✓ /blog/{slug} increments views on each load
✓ /blog/{slug} metatags render in <head>
✓ /blog/{slug} previous/next navigation works
✓ /blog/{slug} for inactive blog returns 404
✓ Admin preview does NOT increment views

Fix everything found. Generate complete corrected files.
Do not skip any step.