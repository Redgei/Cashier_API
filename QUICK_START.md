# 🚀 Cashier Payment System - Quick Start Guide

## Installation & Setup

### 1️⃣ Run Migrations

Create the database tables:

```bash
# Run all pending migrations
php artisan migrate

# To rollback if needed
php artisan migrate:rollback
```

This creates:
- `payments` table
- `receipts` table
- `installments` table
- `refunds` table

---

### 2️⃣ Seed Test Data (Optional)

Populate the database with test data:

```bash
# Run the PaymentSeeder
php artisan db:seed --class=PaymentSeeder

# Or refresh with seeding
php artisan migrate:fresh --seed
```

This creates:
- 5 test users
- 3 payments per user
- Automatic receipts

---

### 3️⃣ Start the Development Server

```bash
php artisan serve
```

Default: `http://localhost:8000`

---

## 🧪 Testing

### Run All Tests
```bash
php artisan test tests/Feature/PaymentTest.php
```

### Run Specific Test
```bash
php artisan test tests/Feature/PaymentTest.php --filter=test_create_payment
```

### Run with Coverage
```bash
php artisan test --coverage
```

---

## 📡 Testing API Endpoints

### Using cURL

#### 1. Create a Payment
```bash
curl -X POST http://localhost:8000/api/payments \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "user_id": 1,
    "amount": 5000.00,
    "payment_method": "credit_card",
    "reference_number": "TXN-20260222-001",
    "description": "Tuition Payment"
  }'
```

#### 2. Get Student Payments
```bash
curl http://localhost:8000/api/payments/1 \
  -H "Accept: application/json"
```

#### 3. Get Payment Receipt
```bash
curl http://localhost:8000/api/payments/1/receipt \
  -H "Accept: application/json"
```

#### 4. Process Refund
```bash
curl -X POST http://localhost:8000/api/payments/1/refund \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "amount": 1000.00,
    "reason": "Student withdrawal",
    "notes": "Course dropped by deadline"
  }'
```

#### 5. List All Payments
```bash
curl "http://localhost:8000/api/payments?status=completed&page=1" \
  -H "Accept: application/json"
```

#### 6. Update Payment Status
```bash
curl -X PUT http://localhost:8000/api/payments/1 \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "status": "completed",
    "notes": "Payment verified"
  }'
```

#### 7. Mark Installment as Paid
```bash
curl -X POST http://localhost:8000/api/installments/1/pay \
  -H "Accept: application/json"
```

---

### Using Postman

1. Create a new POST request
2. URL: `http://localhost:8000/api/payments`
3. Headers:
   - `Content-Type: application/json`
   - `Accept: application/json`
4. Body (raw JSON):
   ```json
   {
     "user_id": 1,
     "amount": 5000.00,
     "payment_method": "credit_card",
     "reference_number": "TXN-20260222-001",
     "description": "Tuition Payment"
   }
   ```
5. Click Send

---

### Using Insomnia

1. New Request → Post
2. URL: `http://localhost:8000/api/payments`
3. Request Body → JSON
4. Paste JSON payload
5. Send

---

## 📚 Documentation Files

| File | Purpose |
|------|---------|
| `CASHIER_API.md` | Complete API documentation with all endpoints |
| `IMPLEMENTATION_SUMMARY.md` | Overview of what was implemented |
| `QUICK_START.md` | This file - quick reference guide |

---

## 🗂️ File Locations

### Models
```
app/Models/
├── Payment.php
├── Receipt.php
├── Installment.php
├── Refund.php
└── User.php (updated)
```

### Controller
```
app/Http/Controllers/
└── PaymentController.php
```

### Routes
```
routes/api.php
```

### Migrations
```
database/migrations/
├── 2026_02_22_121619_create_payments_table.php
├── 2026_02_22_121628_create_receipts_table.php
├── 2026_02_22_121640_create_installments_table.php
└── 2026_02_22_121645_create_refunds_table.php
```

### Factories
```
database/factories/
├── PaymentFactory.php
├── ReceiptFactory.php
├── InstallmentFactory.php
└── RefundFactory.php
```

### Tests
```
tests/Feature/PaymentTest.php
```

---

## 🔧 Common Commands

```bash
# Migrations
php artisan migrate              # Run migrations
php artisan migrate:rollback     # Rollback last batch
php artisan migrate:fresh        # Reset and rerun all

# Seeding
php artisan db:seed --class=PaymentSeeder

# Testing
php artisan test                 # Run all tests
php artisan test --filter=create # Run specific test

# Development
php artisan serve                # Start dev server
php artisan tinker              # Interactive shell

# Optimization
php artisan optimize             # Cache everything
php artisan view:cache          # Cache views
```

---

## ⚙️ Configuration

The payment system is ready to use without additional configuration. 

### Optional: Customize Receipt Prefix

Edit `app/Http/Controllers/PaymentController.php`, in the `generateReceiptNumber()` method:

```php
private function generateReceiptNumber(): string
{
    // Change 'RCP' to your preferred prefix
    return 'RCP-' . date('Ymd') . '-' . str_pad(Receipt::count() + 1, 5, '0', STR_PAD_LEFT);
}
```

---

## 🐛 Debugging

### View Raw SQL
Enable query logging in `config/app.php` or add to controller:

```php
use Illuminate\Support\Facades\DB;

DB::enableQueryLog();
dd(DB::getQueryLog());
```

### Common Issues

**Migration Failed?**
```bash
php artisan migrate:rollback
php artisan migrate
```

**Foreign Key Error?**
- Ensure users table exists: `php artisan migrate`
- Check `user_id` matches actual user IDs

**API Returns 404?**
- Ensure routes are cached: `php artisan route:cache`
- Check base URL: `http://localhost:8000`

---

## 📞 Quick Reference

| Task | Command |
|------|---------|
| Setup | `php artisan migrate && php artisan db:seed --class=PaymentSeeder` |
| Test | `php artisan test tests/Feature/PaymentTest.php` |
| Tinker | `php artisan tinker` then `Payment::all()` |
| Fresh Start | `php artisan migrate:fresh --seed` |
| View Payments | `Payment::with('user', 'receipt')->get()` |

---

## ✨ Next: Custom Modifications

After setup, you might want to:

1. **Add Authentication**: Middleware to `/api/payments` routes
2. **Add Authorization**: Policy classes for Payment model
3. **Add Logging**: Log all payment transactions
4. **Add Notifications**: Email receipts to students
5. **Add PDF Generation**: Generate PDF receipts
6. **Add Reports**: Payment summaries by period
7. **Add Webhooks**: Notify external systems
8. **Add Caching**: Cache frequently accessed payments

---

**Ready to go! Start with `php artisan migrate` 🎉**
