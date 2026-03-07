# ✅ Railway Deployment Checklist

## Pre-Deployment Checklist

### Files Prepared (All Done! ✅)
- [x] `nixpacks.toml` - Railway build configuration
- [x] `Procfile` - Deployment start command
- [x] `railway.json` - Railway project settings
- [x] `.env.railway` - Environment variables template
- [x] `2026_03_06_000000_create_customer_bookmarks_table.php` - Laravel migration
- [x] `BookmarkApiController.php` - Bookmark API endpoints
- [x] `ExamApiController.php` - Updated with gap check & performance
- [x] `api.php` - Updated routes

---

## Deployment Steps

### ☐ Step 1: Push to GitHub (5 minutes)

**If you DON'T have Git installed:**
1. Download and install Git: https://git-scm.com/download/win
2. Restart your terminal after installation

**Push your code:**
```bash
# Navigate to backend folder
cd c:/Users/LENOVO/Desktop/medfellowsapp-main/backend

# Initialize Git (if not already done)
git init

# Add all files
git add .

# Commit
git commit -m "Railway deployment ready - MedFellows backend"

# Create a new repository on GitHub.com first, then:
git remote add origin https://github.com/YOUR_USERNAME/medfellows-backend.git
git branch -M main
git push -u origin main
```

---

### ☐ Step 2: Create Railway Project (3 minutes)

1. **Sign up**: Go to https://railway.app
   - Click "Login with GitHub"
   - Authorize Railway

2. **Create Project**:
   - Click "New Project"
   - Select "Deploy from GitHub repo"
   - Choose your `medfellows-backend` repo

3. **Wait for initial deploy** (will fail - that's okay, we need to add database first)

---

### ☐ Step 3: Add MySQL Database (2 minutes)

1. In your Railway project, click **"+ New"**
2. Select **"Database"** → **"Add MySQL"**
3. Railway will create the database and auto-link it
4. Verify: You should see 2 services now:
   - `medfellows-backend` (your app)
   - `mysql` (database)

---

### ☐ Step 4: Configure Environment Variables (5 minutes)

1. Click on **`medfellows-backend`** service (NOT mysql)
2. Go to **"Variables"** tab
3. Click **"RAW Editor"** button
4. **Copy and paste** everything from `.env.railway` file:

```plaintext
APP_NAME="Medfellows App"
APP_ENV=production
APP_KEY=base64:M7qAvoxaKFTdyQbWJH7t0lwY6bo8xyIKCrRO6j16CYI=
APP_DEBUG=false
APP_URL=${{RAILWAY_PUBLIC_DOMAIN}}

DB_CONNECTION=mysql
DB_HOST=${{MYSQLHOST}}
DB_PORT=${{MYSQLPORT}}
DB_DATABASE=${{MYSQLDATABASE}}
DB_USERNAME=${{MYSQLUSER}}
DB_PASSWORD=${{MYSQLPASSWORD}}

BROADCAST_DRIVER=log
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120
QUEUE_DRIVER=sync

MAIL_MAILER=smtp
MAIL_HOST=ssl0.ovh.net
MAIL_PORT=587
MAIL_USERNAME=kontakt@medfellows.pl
MAIL_PASSWORD=WitkaSkrzypecki2019
MAIL_ENCRYPTION=SSL
MAIL_FROM_NAME="Medfellows App"
MAIL_FROM_ADDRESS=kontakt@medfellows.pl

TPAY_API_KEY=a331b807fa476fb05231942c3af2179379d2ce60
TPAY_API_PASSWORD=Appi@2422

INFAKT_API_KEY=7361d1d825dc25d222b4f2579f571613aaf1ac5f
```

5. Click **"Update Variables"** (this will trigger a redeploy)

---

### ☐ Step 5: Generate Public URL (1 minute)

1. Still in `medfellows-backend` service
2. Go to **"Settings"** tab
3. Scroll to **"Networking"** section
4. Click **"Generate Domain"**
5. **COPY YOUR URL** (e.g., `medfellows-backend-production-abc123.up.railway.app`)

📋 **Write it down here**: ___________________________________

---

### ☐ Step 6: Monitor Deployment (3 minutes)

1. Go to **"Deployments"** tab
2. Click on the latest deployment (should be deploying now)
3. Watch the logs for:
   ```
   ✓ Building with Nixpacks
   ✓ Installing dependencies
   ✓ Running migrations
   ✓ Caching configuration
   ✓ Starting server
   ```

4. Wait for **green "Success"** badge

---

### ☐ Step 7: Test API Endpoints (5 minutes)

Use Postman or any API testing tool:

**Base URL**: `https://YOUR_RAILWAY_URL/api/customer/`

**Test 1: Categories**
```
POST https://YOUR_RAILWAY_URL/api/customer/category
Headers: Content-Type: application/json
Body: { "customer_id": 1 }
Expected: List of categories
```

**Test 2: Topics**
```
POST https://YOUR_RAILWAY_URL/api/customer/topics
Headers: Content-Type: application/json
Body: {}
Expected: List of topics
```

**Test 3: Bookmarks (Add)**
```
POST https://YOUR_RAILWAY_URL/api/customer/bookmarks/add
Headers: Content-Type: application/json
Body: { "customer_id": 1, "question_id": 1 }
Expected: { "status": 1, "message": "Bookmark added successfully" }
```

**Test 4: Bookmarks (Get)**
```
POST https://YOUR_RAILWAY_URL/api/customer/bookmarks
Headers: Content-Type: application/json
Body: { "customer_id": 1 }
Expected: List of bookmarked questions
```

**Test 5: Performance Stats**
```
POST https://YOUR_RAILWAY_URL/api/customer/performance
Headers: Content-Type: application/json
Body: { "customer_id": 1 }
Expected: Performance statistics
```

---

### ☐ Step 8: Connect React Native App (3 minutes)

**Update API Base URL:**

**File**: `medfellowsapp-main/src/api/apiPath.js`

Find the line with `BASE_URL` and change it to your Railway URL:

```javascript
// Before:
const BASE_URL = 'https://medfellows.app/api/customer/';

// After (use YOUR Railway URL):
const BASE_URL = 'https://YOUR_RAILWAY_URL/api/customer/';
```

**Save the file and rebuild your app.**

---

## Final Testing Checklist

### ☐ Frontend App Testing

1. **Categories Screen**:
   - [ ] Opens without errors
   - [ ] Shows loading spinner
   - [ ] Loads categories from Railway backend
   - [ ] Can navigate to specific category

2. **Topics Library**:
   - [ ] Opens without errors
   - [ ] Shows loading spinner
   - [ ] Loads topics from backend
   - [ ] Search functionality works

3. **Bookmarks**:
   - [ ] Can add bookmark from question screen
   - [ ] Bookmark icon changes state
   - [ ] Bookmarked question appears in Bookmarks screen
   - [ ] Can remove bookmark

4. **Premium Screen**:
   - [ ] Opens without errors
   - [ ] "Subscribe Now" button works
   - [ ] Shows success/error message

5. **Progress Screen**:
   - [ ] Gap check shows weak topics
   - [ ] Performance stats display correctly

6. **Home Screen**:
   - [ ] Performance metrics load
   - [ ] Dashboard shows real data

---

## Troubleshooting

### ❌ Build Failed on Railway

**Check:**
1. Go to Deployments → Click failed deployment → View logs
2. Look for error message
3. Common issues:
   - Composer dependencies conflict → Check `composer.json`
   - PHP version mismatch → Check `nixpacks.toml`

**Fix:** Update the problematic file, commit, and push to GitHub (auto-redeploys)

---

### ❌ Database Connection Error

**Check:**
1. MySQL service is running (should show green in Railway dashboard)
2. Environment variables are correct:
   - `${{MYSQLHOST}}` (with double curly braces)
   - `${{MYSQLPORT}}`
   - `${{MYSQLDATABASE}}`
   - `${{MYSQLUSER}}`
   - `${{MYSQLPASSWORD}}`

**Fix:**
1. Go to Variables tab
2. Verify the database variables use Railway's variable references
3. Click "Update Variables" to trigger redeploy

---

### ❌ API Returns 500 Error

**Check:**
1. Set `APP_DEBUG=true` in Railway variables (temporarily)
2. Redeploy
3. Make API request again
4. Check Railway logs for detailed error

**Common causes:**
- Missing route in `api.php`
- Controller file not uploaded
- Database table doesn't exist (migration didn't run)

**Fix:**
1. Check the specific error in logs
2. Verify all files are committed to GitHub
3. Ensure migrations ran successfully

---

### ❌ Migrations Didn't Run

**Check Railway Logs for:**
```
php artisan migrate --force
```

**If migration failed:**
1. Go to Railway project
2. Click on `medfellows-backend` service
3. Click **"Deployments"** tab
4. Click latest deployment
5. In the **"..."** menu, select **"Restart"**

This will re-run the Procfile command including migrations.

---

## Cost Monitoring

**Check Your Usage:**
1. Go to Railway dashboard
2. Click your project name
3. Look at the top right for **credit usage**

**Free $5 credit typically lasts:**
- Light testing: 2-3 weeks
- Heavy testing: 1 week

**When to upgrade:**
- When credit runs out
- Hobby plan ($5/month) is perfect for production

---

## Success! 🎉

If all checkboxes are marked, your MedFellows backend is successfully deployed to Railway!

**What You Have Now:**
- ✅ Live backend API on Railway
- ✅ MySQL database with bookmarks table
- ✅ All 9 screens integrated with real APIs
- ✅ Automatic deployments from GitHub
- ✅ SSL certificate (HTTPS)
- ✅ Professional hosting

**Your Railway URL**: ___________________________________

**Next Steps:**
1. Share the app with beta testers
2. Monitor Railway logs for any errors
3. Set up custom domain (optional)
4. Enable automatic backups

---

**Total Time**: ~30 minutes
**Difficulty**: Easy
**Status**: 🟢 Production Ready

🎊 **Congratulations! Your MedFellows app is now fully deployed!** 🎊
