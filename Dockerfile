# Use the official PHP image
FROM php:8.1-cli

# Set the working directory in the container
WORKDIR /app

# Copy your project files into the container
COPY . /app

# Install any necessary PHP extensions if required (optional)
# RUN docker-php-ext-install pdo pdo_mysql

# Expose the port Render uses (10000)
EXPOSE 10000

# Start the PHP built-in server
CMD ["php", "-S", "0.0.0.0:10000", "-t", "."]
