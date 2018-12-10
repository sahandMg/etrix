export {
    auth, authGoogle,
    logout, authFail,
    registerUser,
    authCheckState
} from './auth';

export { addToCart, changeNumFromCart, removeAllCart, restoreCart, updateCart, updateCartPrices, addProductPrice,
    removeFromCart, getCartFromLocalStorage, getCartFromServer, setLoadingAndError } from './cart'