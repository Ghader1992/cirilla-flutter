// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'syriatel_otp_store.dart';

// **************************************************************************
// StoreGenerator
// **************************************************************************

// ignore_for_file: non_constant_identifier_names, unnecessary_brace_in_string_interps, unnecessary_lambdas, prefer_expression_function_bodies, lines_longer_than_80_chars, avoid_as, avoid_annotating_with_dynamic, no_leading_underscores_for_local_identifiers

mixin _$SyriatelOtpStore on SyriatelOtpStoreBase, Store {
  Computed<bool>? _$loadingComputed;

  @override
  bool get loading => (_$loadingComputed ??= Computed<bool>(() => super.loading,
          name: 'SyriatelOtpStoreBase.loading'))
      .value;

  late final _$_loadingAtom =
      Atom(name: 'SyriatelOtpStoreBase._loading', context: context);

  @override
  bool get _loading {
    _$_loadingAtom.reportRead();
    return super._loading;
  }

  @override
  set _loading(bool value) {
    _$_loadingAtom.reportWrite(value, super._loading, () {
      super._loading = value;
    });
  }

  late final _$sendOtpAsyncAction =
      AsyncAction('SyriatelOtpStoreBase.sendOtp', context: context);

  @override
  Future<bool> sendOtp(String phone) {
    return _$sendOtpAsyncAction.run(() => super.sendOtp(phone));
  }

  late final _$verifyOtpAsyncAction =
      AsyncAction('SyriatelOtpStoreBase.verifyOtp', context: context);

  @override
  Future<bool> verifyOtp(String phone, String otp) {
    return _$verifyOtpAsyncAction.run(() => super.verifyOtp(phone, otp));
  }

  @override
  String toString() {
    return '''
loading: ${loading}
    ''';
  }
}
