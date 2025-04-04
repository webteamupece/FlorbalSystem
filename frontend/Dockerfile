# Use official Node.js LTS image
FROM node:18

# Set working directory
WORKDIR /app

# Copy only package files first for caching
COPY package*.json ./

# Install dependencies
RUN npm install

# Copy rest of the app
COPY . .

# Ensure Vite binary is executable (fix for "Permission denied")
RUN chmod +x ./node_modules/.bin/vite

# Expose the Vite dev server port
EXPOSE 5173

# Run the Vite dev server (host mode so it's accessible from your host)
CMD ["npm", "run", "dev", "--", "--host"]
