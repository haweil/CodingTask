# URL Shortener Service

A scalable URL shortening service with fast redirects and detailed analytics.

## Setup, Installation, and Running Instructions

### Prerequisites
- **Docker & Docker Compose**
- **PHP**: ^8.2
- **Laravel Framework**: ^10.0

## Deploying Using Docker Container
1. **Clone the Repository**:
   ```bash
   git clone https://github.com/haweil/CodingTask
   cd CodingTask
2. **Setup Environment**:
```bash
cp .env.example .env
```
3. **Run with Docker**:
```bash
docker-compose up -d
```
4. **Install Dependencies**:
 ```bash
docker-compose exec app composer install
```
5. **Run Migrations**:
``` bash
docker-compose exec app php artisan migrate
```
6. **Generate App Key:**:
``` bash
docker-compose exec app php artisan key:generate
```
## URL Shortener Application Flow 
### **Shortening**  
- **Endpoint:** `POST /api/shorten`  
- **Process:**  
  - Generates a **unique 6-character alias**.  
  - Stores it in **MySQL (`short_urls`)** and **Redis (`short_url:{alias}`)**.  
### **Redirecting**  
- **Endpoint:** `GET /{alias}`  
- **Process:**  
  - Fetches the **original URL** from **Redis** (or MySQL if cache miss).  
  - Logs redirect data in **`redirect_data`** table.  
  - Redirects the user to the original URL.  


###  **Analytics**  
- **Endpoint:** `GET /analytics/{alias}`  
- **Process:**  
  - Aggregates **redirect stats** from `redirect_data` .  
  - **Metrics Tracked:**  
    - **Total redirects count**
    - **Redirects per day**  
    - **Geo distribution** 
    - **User-Agent distribution** 
  - Cached in **Redis for 1 hour** for performance.  
  - **Cache is invalidated** when a new redirect occurs to ensure accuracy.  

---

### **Notes**  
- **Caching:**  
  - **URLs:** Stored in Redis for **24 hours**.  
  - **Analytics:** Stored in Redis for **1 hour**, invalidated on **new redirects**.  

- **Dependencies:**  
  - Ensure **Docker services** (`app`, `redis`, `mysql`) are **running** before testing.  
