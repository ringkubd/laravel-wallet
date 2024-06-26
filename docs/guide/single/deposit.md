# Deposit

A deposit is a sum of money which is part of the full price of something, 
and which you pay when you agree to buy it.

In this case, the Deposit is the replenishment of the wallet.

## User Model

<!--@include: ../../_include/models/user_simple.md -->

## Make a Deposit

Find user:

```php
$user = User::first(); 
```

As the user uses `HasWallet`, he will have `balance` property. 
Check the user's balance.

```php
$user->balance; // 0
$user->balanceInt; // 0
```

The balance is zero, which is what we expected.
Put it on his 10 cents account.

```php
$user->deposit(10); 
$user->balance; // 10
$user->balanceInt; // 10
```

Wow! The balance is 10 cents, the money is credited.
