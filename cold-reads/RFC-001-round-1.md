# Cold Read Report: RFC-001 (Round 1)

- **RFC**: `RFCs/RFC-001-Foundation-Auth-Catalog.md`
- **Predecessors Read**: `none`
- **Result**: **NEEDS REVISION** (`4` `BEHAVIOR_OR_INTERFACE` guesses found, `1` `IMPLEMENTER_CHOICE` noted)

## Findings

1. **Post-Login & Post-Registration Redirect Target and Breeze `/dashboard` Route (`Section 2.3`, `Section 2.5`, `AC-7`, `AC-8`)**
   - **Classification**: `BEHAVIOR_OR_INTERFACE`
   - **Issue**: Laravel Breeze scaffolds `/dashboard` and redirects there after login/register. RFC-001 did not specify whether `/dashboard` redirects to `/menus` or where login/register redirects when no `url.intended` is present.
   - **Resolution**: Pin in RFC-001 that default post-login/post-registration redirect (when no `url.intended` exists) goes to `/menus` (`route('menus.index')`), and `GET /dashboard` redirects to `/menus`.

2. **Available Menu Card Action Element on `GET /menus` Before `POST /cart/items` Exists (`Section 3`, `AC-5`, `AC-6`, `UI-2`)**
   - **Classification**: `BEHAVIOR_OR_INTERFACE`
   - **Issue**: RFC-001 mentioned that the add-to-cart button for available menus would be wired to `POST /cart/items` in RFC-002, while `UI-2` forbids dead buttons.
   - **Resolution**: Pin in RFC-001 that `GET /menus` renders availability status badges (**"Tersedia"** for `is_available = true` and **"Habis"** for `is_available = false`) without dead buttons in RFC-001; RFC-002 adds the active `POST /cart/items` form/button on available menu cards and disabled button on unavailable cards.

3. **`App\Models\Order` and `App\Models\OrderItem` Eloquent Relation Names & Casts (`Section 2.2`, `Section 3`, `AC-3`)**
   - **Classification**: `BEHAVIOR_OR_INTERFACE`
   - **Issue**: Method names for relations on `Order` (`items()` vs `orderItems()`, `user()`) and `OrderItem` (`order()`, `menu()`) and attribute casts were not pinned in Section 3.
   - **Resolution**: Explicitly pin all model relation method names and casts in RFC-001 Section 3.

4. **Scope of Rule `OPS-1` in RFC-001 (`Header`, `Section 2.4`, `AC-4`)**
   - **Classification**: `BEHAVIOR_OR_INTERFACE`
   - **Issue**: `OPS-1` in `RULES.md` requires both `DatabaseSeeder` static images AND `vercel.json` / `api/index.php` (Feature `F13`, which belongs to RFC-003). Listing `OPS-1` in RFC-001's header creates ambiguity during `/review-rfc RFC-001`.
   - **Resolution**: Remove `OPS-1` from RFC-001's `**Rules**:` header (keep `OPS-1` in RFC-003 where `F13` is implemented; `AC-4` in RFC-001 already enforces static seeder images in `public/images/menus/`).

5. **Exact Rupiah Currency Formatting Pattern (`Section 2.5`, `AC-5`)**
   - **Classification**: `IMPLEMENTER_CHOICE`
   - **Resolution**: Record in RFC-001 Section 4 (*Implementer's Choices*).
