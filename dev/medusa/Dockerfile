FROM node:20

WORKDIR /app/medusa

COPY package.json .
COPY package-lock.json .
COPY develop.sh .

RUN apt-get update

RUN apt-get install -y python

RUN npm i -g @medusajs/medusa-cli@latest

RUN npm i

COPY . .

ENTRYPOINT ["sh", "./develop.sh"]
