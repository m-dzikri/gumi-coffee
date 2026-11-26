# Cold Read Report: RFC-002 (Round 1)

- **RFC**: `RFCs/RFC-002-Cart-Checkout-Customer-Orders.md`
- **Predecessors Read**: `RFC-001`
- **Result**: **NEEDS REVISION** (`4` `BEHAVIOR_OR_INTERFACE` guesses found, `1` `IMPLEMENTER_CHOICE` converted to explicit contract)

## Findings

1. **Retrieving a Deleted Menu's Name for Checkout Validation Errors (`Section 2.1`, `Section 2.2`, `AC-5`)**
   - **Classification**: `BEHAVIOR_OR_INTERFACE`
   - **Issue**: If a menu in `session('cart')` is deleted from the `menus` table before `POST /checkout`, `AC-5` requires the error message to include that menu's name, which would be lost if `session('cart')` only stored `menu_id` and `quantity`.
   - **Resolution**: Include `'menu_name' => $menu->name` in each `session('cart')` entry solely as a fallback label when the database row no longer exists (while still reading live price and availability from the `menus` table).

2. **Guest Redirect Behavior in `EnsureUserIsAdmin` & Testable Route for `AC-10` (`Section 2.4`, `Section 3`, `AC-10`)**
   - **Classification**: `BEHAVIOR_OR_INTERFACE`
   - **Issue**: RFC-002 did not expose any `/admin` route in Section 3 to test `AC-10`, and `EnsureUserIsAdmin` showed `abort(403)` for `!$request->user()`.
   - **Resolution**: Pin that `EnsureUserIsAdmin` redirects unauthenticated requests to `route('login')` and returns `403` for authenticated non-admin users, and expose `GET /admin` (`admin.dashboard`, middleware `['web', 'auth', 'admin']`) in RFC-002 (which redirects to or displays the admin orders page in RFC-003).

3. **Exact Relationship Method Names and Attribute Casts on `App\Models\Order` (`Section 2.2`, `Section 2.3`, `AC-6`, `AC-7`)**
   - **Classification**: `BEHAVIOR_OR_INTERFACE`
   - **Issue**: Relation names (`Order::items()`, `Order::user()`, `OrderItem::order()`, `OrderItem::menu()`) and casts (`paid_at => 'datetime'`) were not pinned.
   - **Resolution**: Pin explicitly in RFC-001 Section 3 and reference in RFC-002.

4. **`ValidationException` Error Key Names Across Cart, Checkout, and State Machine Methods (`Section 2.1`, `Section 2.2`, `Section 2.3`, `AC-1`, `AC-4`, `AC-5`, `AC-7`, `AC-8`)**
   - **Classification**: `BEHAVIOR_OR_INTERFACE`
   - **Issue**: The error bag keys passed to `ValidationException::withMessages([...])` were unspecified.
   - **Resolution**: Pin deterministic error keys: `'cart'` for cart business rule rejections (`is_available = false`, cumulative quantity `> 99`, or missing cart item), `'checkout'` for checkout guard rejections (empty cart, outside operating hours, or item became unavailable/deleted), `'payment_status'` for payment mutation rejections (`payQrisByCustomer()`, `markCashPaidByAdmin()`), and `'status'` for status transition/cancellation rejections (`cancelByCustomer()`, `transitionStatusByAdmin()`).

5. **Route Parameter Binding on `PATCH /cart/items/{menuId}` and `DELETE /cart/items/{menuId}` (`Section 2.1`, `Section 3`, `AC-1`)**
   - **Classification**: `IMPLEMENTER_CHOICE` -> Pinned as interface contract
   - **Resolution**: Pin `{menuId}` as a raw integer route parameter (not implicit `Menu` model binding) so a deleted menu can still be removed from `session('cart')` via `DELETE /cart/items/{menuId}` without a `404`, while `PATCH /cart/items/{menuId}` verifies the item exists in `session('cart')` and is still `is_available = true` in `menus`.
