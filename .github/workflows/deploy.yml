steps:
  - name: Prepare Environment Variables
    run: |
      # Try to get STUDENT_ID from .env.example if it exists (prioritize repo config)
      if [ -f .env.example ]; then
        export STUDENT_ID=$(grep '^STUDENT_ID=' .env.example | cut -d '=' -f2 | tr -d '\r ')
      elif [ -f .env ]; then
         export STUDENT_ID=$(grep '^STUDENT_ID=' .env | cut -d '=' -f2 | tr -d '\r ')
      fi
      
      if [ ! -z "$STUDENT_ID" ]; then
        export STUDENT_NAME=$STUDENT_ID
      else
        export STUDENT_NAME=$(whoami)
      fi
      
      echo "STUDENT_NAME=${STUDENT_NAME}" >> $GITHUB_ENV
      mkdir -p /tmp/env-backups/${STUDENT_NAME}
      echo "BACKUP_DIR=/tmp/env-backups/${STUDENT_NAME}" >> $GITHUB_ENV

      # 🟢 ขั้นตอนแก้ไข: ล้างสิทธิ์ไฟล์เก่าก่อน Checkout (เพื่อแก้ Error ใน image_f261e3.png)
  - name: Pre-cleanup Permissions
    run: |
      docker run --rm -v $(pwd):/work -w /work busybox chown -R $(id -u):$(id -g) . || true

  - name: Backup .env
    run: |
      if [ -f .env ]; then
        cp .env ${{ env.BACKUP_DIR }}/.env.backup
        echo "Backed up .env to ${{ env.BACKUP_DIR }}/.env.backup"
      fi

  - uses: actions/checkout@v4

  - name: Restore and Update .env
    run: |
      # 1. จัดการตัวไฟล์ .env ก่อน
      if [ -f ${{ env.BACKUP_DIR }}/.env.backup ]; then
        cp ${{ env.BACKUP_DIR }}/.env.backup .env
        echo "Restored .env from backup"
      else
        cp .env.example .env
        echo "Created .env from .env.example"
      fi

      # 2. 🟢 ประกาศฟังก์ชันไว้นอก if เพื่อให้เรียกใช้ได้ทุกครั้ง
      update_env() {
        sed -i "/^#*$1=/d" .env
        echo "$1=$2" >> .env
      }

      # 3. 🟢 Force Sync Config from .env.example (Read from .env.example for everything)
      # This ensures .env always matches the repo's config for these keys
      # Sync config keys from .env.example (ยกเว้น DB_HOST ที่ต้อง set แบบ dynamic)
      KEYS="STUDENT_ID STUDENT_NAME COMPOSE_PROJECT_NAME STUDENT_PORT FORWARD_DB_PORT DB_CONNECTION DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD"
      
      for KEY in $KEYS; do
         VAL=$(grep "^$KEY=" .env.example | cut -d '=' -f2- | tr -d '\r')
         if [ ! -z "$VAL" ]; then
            update_env "$KEY" "$VAL"
         fi
      done

      # 🟢 Set DB_HOST dynamically ให้ตรงกับ container name จริง
      update_env "DB_HOST" "${STUDENT_NAME}-db"

  - name: Stop Existing Containers
    run: |
      STUDENT_NAME=${{ env.STUDENT_NAME }}
      # หยุดและลบ container เก่าที่อาจค้างอยู่ (ไม่ว่าจะสร้างจาก project ใด)
      docker stop ${STUDENT_NAME}-app ${STUDENT_NAME}-nginx ${STUDENT_NAME}-db 2>/dev/null || true
      docker rm   ${STUDENT_NAME}-app ${STUDENT_NAME}-nginx ${STUDENT_NAME}-db 2>/dev/null || true
      # ลบ compose project เก่าถ้ายังมี (orphan cleanup)
      docker compose -p ${STUDENT_NAME} down --remove-orphans 2>/dev/null || true

  - name: Docker Deploy
    run: |
      # ใช้ค่าพอร์ตจาก .env.example
      STUDENT_NAME=${{ env.STUDENT_NAME }} \
      STUDENT_PORT=$(grep '^STUDENT_PORT=' .env.example | cut -d '=' -f2 | tr -d '\r ' || echo "80") \
      docker compose -p ${{ env.STUDENT_NAME }} up -d --build --force-recreate

  - name: Laravel Post-Deploy
    run: |
      APP_CONTAINER="${{ env.STUDENT_NAME }}-app"
      
      # 🟢 1. แก้สิทธิ์ไฟล์เหมือนเดิม
      docker exec -u root $APP_CONTAINER chown -R www-data:www-data storage bootstrap/cache .env
      docker exec -u root $APP_CONTAINER chmod -R 775 storage bootstrap/cache
      docker exec -u root $APP_CONTAINER chmod 664 .env

      # 🟢 2. ล้างแคชเพื่อให้เห็นการเปลี่ยนแปลงของ .env
      docker exec $APP_CONTAINER php artisan config:clear
      
      # 🟢 3. รันคำสั่งอื่น ๆ ตามลำดับ
      docker exec $APP_CONTAINER php artisan key:generate --force
      docker exec $APP_CONTAINER php artisan migrate --force
      docker exec $APP_CONTAINER php artisan storage:link || true
      
  - name: System Cleanup
    run: |
      docker image prune -f
      docker container prune -f --filter "until=24h"