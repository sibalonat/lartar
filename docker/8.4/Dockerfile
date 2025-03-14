FROM ubuntu:24.04

LABEL maintainer="Marin Nikolli"

ARG WWWGROUP
ARG NODE_VERSION=22
ARG MYSQL_CLIENT="mysql-client"
ARG POSTGRES_VERSION=17

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC
ENV SUPERVISOR_PHP_COMMAND="/usr/bin/php -d variables_order=EGPCS /var/www/html/artisan serve --host=0.0.0.0 --port=80"
# ENV TAURI_COMMAND="/usr/bin/php -d variables_order=EGPCS /var/www/html/artisan serve:desktop --keep-running'"
ENV SUPERVISOR_PHP_USER="sail"
ARG INCLUDE_USER_SETUP=true

# Set timezone
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Set timezone
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Core dependencies
RUN apt-get update && apt-get upgrade -y \
    && mkdir -p /etc/apt/keyrings \
    && apt-get install -y software-properties-common \
    build-essential \
    && add-apt-repository main \
    && add-apt-repository universe \
    && add-apt-repository restricted \
    && add-apt-repository multiverse \
    && apt-get update \
    && apt-get install -y gnupg gosu curl ca-certificates zip unzip git supervisor sqlite3 libcap2-bin libpng-dev python3 dnsutils

# Configure apt for better package management
RUN echo "Acquire::http::Pipeline-Depth 0;" > /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::http::No-Cache true;" >> /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::BrokenProxy true;" >> /etc/apt/apt.conf.d/99custom

# Setup Rust
RUN curl --proto '=https' --tlsv1.2 -sSf https://sh.rustup.rs | sh -s -- -y && \
    . $HOME/.cargo/env && \
    rustup default stable && \
    rustup component add rust-src

# Install PHP and system dependencies
RUN curl -sS 'https://keyserver.ubuntu.com/pks/lookup?op=get&search=0xb8dc7e53946656efbce4c1dd71daeaab4ad4cab6' | gpg --dearmor | tee /etc/apt/keyrings/ppa_ondrej_php.gpg > /dev/null \
    && echo "deb [signed-by=/etc/apt/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu noble main" > /etc/apt/sources.list.d/ppa_ondrej_php.list \
    && apt-get update \
    && apt-get install -y php8.4-cli php8.4-dev \
       php8.4-pgsql php8.4-sqlite3 php8.4-gd \
       php8.4-curl php8.4-mongodb \
       php8.4-imap php8.4-mysql php8.4-mbstring \
       php8.4-xml php8.4-zip php8.4-bcmath php8.4-soap \
       php8.4-intl php8.4-readline \
       php8.4-ldap \
       php8.4-msgpack php8.4-igbinary php8.4-redis php8.4-swoole \
       php8.4-memcached php8.4-pcov php8.4-imagick php8.4-xdebug \
    && curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_VERSION.x nodistro main" > /etc/apt/sources.list.d/nodesource.list \
    && apt-get update \
    && apt-get install -y nodejs \
    && npm install -g npm

# Database clients (if needed)
RUN curl -sS https://www.postgresql.org/media/keys/ACCC4CF8.asc | gpg --dearmor | tee /etc/apt/keyrings/pgdg.gpg >/dev/null \
    && echo "deb [signed-by=/etc/apt/keyrings/pgdg.gpg] http://apt.postgresql.org/pub/repos/apt noble-pgdg main" > /etc/apt/sources.list.d/pgdg.list \
    && apt-get update \
    && apt-get install -y $MYSQL_CLIENT \
    && apt-get install -y postgresql-client-$POSTGRES_VERSION

# Install Tauri dependencies (minimal for headless mode)
RUN apt-get update && apt-get install -y \
    # Core Tauri dependencies only
    libgtk-4-dev \
    libwebkit2gtk-4.1-dev \
    libsoup-3.0-dev \
    libjavascriptcoregtk-4.1-dev \
    libadwaita-1-dev \
    libayatana-appindicator3-dev \
    librsvg2-dev \
    libssl-dev \
    patchelf \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Ensure the Cargo bin directory is in the PATH
ENV PATH="/root/.cargo/bin:${PATH}"

ENV PKG_CONFIG_PATH=/usr/lib/pkgconfig:/usr/share/pkgconfig
ENV DISPLAY=:99

# Add this to your Dockerfile or set in your environment
ENV RUSTFLAGS=--cfg=web_sys_unstable_apis
# ENV TAURI_SKIP_DEVSERVER_CHECK=1
# ENV TAURI_CLI_SKIP_SLOW_DEVICES_CHECK=1
# When building in CI
ENV CI=true

# Laravel Vite bypass (in case we need it)
ENV LARAVEL_BYPASS_ENV_CHECK=1

# Set up XDG directories
# ENV XDG_RUNTIME_DIR=/tmp/runtime-dir
ENV GSETTINGS_BACKEND=memory
ENV DCONF_PROFILE=/dev/null
ENV XDG_DATA_HOME=/home/sail/.local/share
ENV XDG_CONFIG_HOME=/home/sail/.config
ENV XDG_CACHE_HOME=/home/sail/.cache

# OpenGL/Mesa configuration for software rendering
ENV LIBGL_ALWAYS_SOFTWARE=1
ENV MESA_LOADER_DRIVER_OVERRIDE=swrast
ENV EGL_PLATFORM=surfaceless
ENV LIBGL_DRI3_DISABLE=1

# Conditionally remove the default Ubuntu user and create the sail user
RUN if [ "$INCLUDE_USER_SETUP" = "true" ]; then \
        userdel -r ubuntu && \
        groupadd --force -g $WWWGROUP sail && \
        useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 sail; \
    fi

# Allow PHP to bind to low-numbered ports
RUN setcap "cap_net_bind_service=+ep" /usr/bin/php8.4

# Create the render group and add sail user to it
RUN if [ "$INCLUDE_USER_SETUP" = "true" ]; then \
        groupadd --system render && \
        usermod -aG render sail; \
    fi

# Create necessary directories and set permissions
RUN if [ "$INCLUDE_USER_SETUP" = "true" ]; then \
    mkdir -p /tmp/runtime-dir && \
    chmod 700 /tmp/runtime-dir && \
    chown sail:sail /tmp/runtime-dir; \
fi

# In your Dockerfile
RUN mkdir -p /tmp/.X11-unix && \
    chmod 1777 /tmp/.X11-unix

# RUN if [ "$INCLUDE_USER_SETUP" = "true" ]; then \
#     mkdir -p /home/sail/.cargo && \
#     cp -r /root/.cargo/* /home/sail/.cargo/ && \
#     chown -R sail:sail /home/sail/.cargo && \
#     rustup default stable && \
#     echo 'export PATH=$HOME/.cargo/bin:$PATH' >> /home/sail/.bashrc; \
# fi
RUN if [ "$INCLUDE_USER_SETUP" = "true" ]; then \
    # Copy Rust setup to sail user
    mkdir -p /home/sail/.cargo && \
    cp -r $HOME/.cargo/* /home/sail/.cargo/ && \
    mkdir -p /home/sail/.rustup && \
    cp -r $HOME/.rustup/* /home/sail/.rustup/ && \
    chown -R sail:sail /home/sail/.cargo /home/sail/.rustup; \
fi

COPY tauri-run.sh /usr/local/bin/tauri-run.sh
RUN chmod +x /usr/local/bin/tauri-run.sh
ENV TAURI_COMMAND="/usr/local/bin/tauri-run.sh"

# Copy configuration files
COPY start-container /usr/local/bin/start-container
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY php.ini /etc/php/8.4/cli/conf.d/99-sail.ini
RUN chmod +x /usr/local/bin/start-container

# Expose port 80
EXPOSE 80/tcp

# Set the entrypoint
ENTRYPOINT ["start-container"]
