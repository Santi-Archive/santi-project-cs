import requests
import tkinter

headers = {
    "User-Agent": "MyWarframeBot/1.0",
    "Accept": "application/json"
}

url = "https://api.warframe.market/v1/items/nova_prime_set/orders"
response = requests.get(url, headers=headers)

data = response.json()
orders = data["payload"]["orders"]

sell_orders = []
for order in orders:
    if order["order_type"] == "sell":
        sell_orders.append(order)

online_seller = []
for order in sell_orders:
    if "user" in order and "status" in order["user"] and order["user"]["status"] == "ingame":
        online_seller.append(order)

cheap_price = []
for order in online_seller:
    if "platinum" in order and order["platinum"] < 119:
        cheap_price.append(order)

print(cheap_price)