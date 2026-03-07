# 🚀 Railway Deployment Guide - MedFellows Backend

## ✅ Prerequisites
- GitHub account
- Railway account (sign up at [railway.app](https://railway.app))

---

## 📦 Step 1: Prepare Your Code (Already Done!)

Your backend is now Railway-ready with these files:
- ✅ `nixpacks.toml` - Railway build configuration
- ✅ `Procfile` - Start command with auto-migrations
- ✅ `railway.json` - Railway deployment settings
- ✅ `.env.railway` - Environment variables template

---

## 🔧 Step 2: Push to GitHub

### Option A: Using Git Bash (Recommended)

1. Open Git Bash in the backend folder:
   ```bash
   cd c:/Users/LENOVO/Desktop/medfellowsapp-main/backend
   ```

2. Initialize and push to GitHub:
   ```bash
   git init
   git add .
   git commit -m "Initial commit - Railway deployment ready"

   # Create a new repo on GitHub first, then:
   git remote add origin https://github.com/YOUR_USERNAME/medfellows-backend.git
   git branch -M main
   git push -u origin main
   ```

### Option B: Using GitHub Desktop
1. Open GitHub Desktop
2. Add the backend folder as a new repository
3. Publish to GitHub

---

## 🚂 Step 3: Deploy to Railway

### 3.1 Create New Project

1. Go to [railway.app](https://railway.app)
2. Click **"New Project"**
3. Select **"Deploy from GitHub repo"**
4. Authorize Railway to access your GitHub
5. Select your `medfellows-backend` repository

### 3.2 Add MySQL Database

1. In your Railway project dashboard, click **"+ New"**
2. Select **"Database"** → **"Add MySQL"**
3. Railway will automatically create and link the database
4. MySQL environment variables (`MYSQLHOST`, `MYSQLPORT`, etc.) are auto-injected

### 3.3 Configure Environment Variables

1. Click on your backend service (not the database)
2. Go to the **"Variables"** tab
3. Click **"RAW Editor"**
4. Copy and paste ALL variables from `.env.railway` file:

```env
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

5. Click **"Update Variables"**

### 3.4 Generate Public Domain

1. Go to **"Settings"** tab of your backend service
2. Scroll to **"Networking"** section
3. Click **"Generate Domain"**
4. Copy your public URL (e.g., `medfellows-backend-production.up.railway.app`)

---

## ✅ Step 4: Verify Deployment

### Check Deployment Logs

1. Go to **"Deployments"** tab
2. Click on the latest deployment
3. Watch the build logs - you should see:
   ```
   ✓ Installing Composer dependencies
   ✓ Running migrations
   ✓ Caching configuration
   ✓ Caching routes
   ✓ Starting server
   ```

### Test API Endpoints

Once deployed, test your endpoints using the Railway public URL:

**Base URL**: `https://your-app.up.railway.app/api/customer/`

**Test Endpoints**:
```bash
# 1. Categories
POST https://your-app.up.railway.app/api/customer/category
Body: { "customer_id": 1 }

# 2. Topics
POST https://your-app.up.railway.app/api/customer/topics

# 3. Bookmarks
POST https://your-app.up.railway.app/api/customer/bookmarks
Body: { "customer_id": 1 }

# 4. Performance Stats
POST https://your-app.up.railway.app/api/customer/performance
Body: { "customer_id": 1 }

# 5. Focus Areas
POST https://your-app.up.railway.app/api/customer/focusareas
Body: { "customer_id": 1 }
```

---

## 📱 Step 5: Connect React Native App

Update your React Native app's API base URL:

**File**: `medfellowsapp-main/src/api/apiPath.js`

```javascript
// Change this:
const BASE_URL = 'https://medfellows.app/api/customer/';

// To your Railway URL:
const BASE_URL = 'https://your-app.up.railway.app/api/customer/';
```

---

## 🎯 Step 6: Test Everything

### 6.1 Test Bookmarks
1. Open your React Native app
2. Navigate to a question screen
3. Tap the bookmark icon
4. Check if it saves (should show as bookmarked)
5. Go to Bookmarks screen - should see the saved bookmark

### 6.2 Test Categories
1. Navigate to Exam Categories
2. Should load categories from Railway backend
3. Should see loading spinner first

### 6.3 Test Topics
1. Go to Topics Library
2. Should load all topics
3. Search should work

### 6.4 Test Premium
1. Go to Premium screen
2. Click "Subscribe Now"
3. Should process payment (or show error if test mode)

---

## 🐛 Troubleshooting

### Build Failed
**Issue**: Composer install fails
**Fix**: Check PHP version in `nixpacks.toml` matches your `composer.json` requirements

### Database Connection Failed
**Issue**: Cannot connect to MySQL
**Fix**:
1. Ensure MySQL service is running
2. Check environment variables are correctly set with Railway syntax: `${{MYSQLHOST}}`
3. Make sure database and backend are in the same Railway project

### Migrations Failed
**Issue**: `php artisan migrate --force` fails
**Fix**:
1. Check Railway logs for specific error
2. Ensure `customer_bookmarks` migration file exists in `database/migrations/`
3. Check database permissions

### 500 Server Error
**Issue**: API returns 500 error
**Fix**:
1. Set `APP_DEBUG=true` temporarily to see detailed errors
2. Check Railway logs: Click "Deployments" → Latest deployment → View logs
3. Ensure `APP_KEY` is set correctly

---

## 💰 Cost Breakdown

### Railway Pricing:
- **Free Trial**: $5 credit (lasts ~2-3 weeks for testing)
- **Hobby Plan**: $5/month (enough for production)
- **Pro Plan**: $20/month (if you need more resources)

### What You Get:
- ✅ Automatic deployments from GitHub
- ✅ Free MySQL database
- ✅ SSL certificate (HTTPS)
- ✅ Auto-scaling
- ✅ 99.9% uptime
- ✅ Real-time logs
- ✅ Easy rollbacks

---

## 🎉 Success Checklist

- [ ] Code pushed to GitHub
- [ ] Railway project created
- [ ] MySQL database added
- [ ] Environment variables configured
- [ ] Public domain generated
- [ ] Deployment successful (green checkmark)
- [ ] All API endpoints responding
- [ ] React Native app connected to Railway URL
- [ ] Bookmarks working end-to-end
- [ ] Categories loading from backend
- [ ] Topics loading from backend
- [ ] Premium subscription functional
- [ ] No errors in Railway logs

---

## 📞 Support

If you encounter any issues:

1. **Check Railway Logs**: Most issues show up here
2. **Railway Discord**: [railway.app/discord](https://discord.gg/railway)
3. **Railway Docs**: [docs.railway.app](https://docs.railway.app)

---

## 🚀 Next Steps After Deployment

1. **Monitor Usage**: Check Railway dashboard for resource usage
2. **Set Up Monitoring**: Add error tracking (Sentry, Bugsnag)
3. **Custom Domain**: Add your own domain in Railway settings
4. **Backups**: Set up automatic database backups
5. **CI/CD**: Enable automatic deployments on Git push

---

**Total Deployment Time**: ~10-15 minutes
**Difficulty**: Easy
**Cost**: Free for testing, $5/month for production

🎊 **Your MedFellows backend is now Railway-ready!**
